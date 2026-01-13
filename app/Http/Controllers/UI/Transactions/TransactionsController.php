<?php

namespace App\Http\Controllers\UI\Transactions;

use App\Http\Abstraction\AccountAbstract;
use App\Models\BudgetItem;
use App\Models\CheckingAccount;
use App\Models\Counterparty;
use App\Models\OPFType;
use App\Models\Project;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class TransactionsController extends AccountAbstract
{



    /**
     * Получить список транзакций
     * Get transactions
     * @param Request $request
     * @param null $subdomain
     * @return \App\Http\Responses\JsonResponse
     */
    public function getList(Request $request, $subdomain = null){

        /* STEP: Prepare dependencies
        -------------------------------------------*/
            $dependencies = [
                'account',
                'fromCheckingAccount',
                'toCheckingAccount',
                'counterparty' => function($query){ $query->withTrashed()->with('category'); },
                'budgetItem',
                'project',
            ];


        /* STEP: Get transactions
        -------------------------------------------*/
            $transactions = $this->getModel(false, true, [$this, 'applyFilters']);
            $transactions->with($dependencies);

            // Sort
            $transactions->orderByDesc('date');

            $data['transactions'] = $transactions->get();
            if(!empty($this->paginator))
                $data['last_page'] = $this->paginator->lastPage();


        /* STEP: Calculate counters
        -------------------------------------------*/
            $incomes = $this->getBuilder(Transaction::class)->whereType('income');
            $this->applyFilters($incomes);
            $data['income_count'] = $incomes->count();
            $data['income_sum'] = $incomes->sum('amount');


            $expenses = $this->getBuilder(Transaction::class)->whereType('expense');
            $this->applyFilters($expenses);
            $data['expense_count'] = $expenses->count();
            $data['expense_sum'] = $expenses->sum('amount');

        return $this->success($data);
    }






    /**
     * Создание транзакции
     * @param Request $request
     * @return \App\Http\Responses\JsonResponse
     */
    public function create(Request $request){

        try{
            $transaction = null;

            DB::transaction(function() use ($request, $transaction) {

                /* STEP: Get Request Data
                -------------------------------------------*/
                    // Get updates
                    $data = $this->getValidatedData();
                    if(!$data)
                        throw new \Exception($this->lastValidationError);

                    $date = $data['date'];


                /* STEP: Create Transaction
                -------------------------------------------*/
                    $transaction = new Transaction($data);
                    $transaction->account()->associate($this->account);



                /* STEP: Assign associates
                -------------------------------------------*/

                    // From checking account
                    if(!empty($data['from_ca_id'])){
                        $checkingAccount = $this->getBuilder(CheckingAccount::class, [
                            'id' => $data['from_ca_id']
                        ])->first();
                        if(!$checkingAccount)
                            throw new \Exception('Расчетный счет не найден');

                        $transaction->fromCheckingAccount()->associate($checkingAccount);
                    }


                    // To checking account
                    if(!empty($data['to_ca_id'])){
                        $checkingAccount = $this->getBuilder(CheckingAccount::class, [
                            'id' => $data['to_ca_id']
                        ])->first();
                        if(!$checkingAccount)
                            throw new \Exception("Расчетный счет не найден");

                        $transaction->toCheckingAccount()->associate($checkingAccount);
                    }


                    // Counterparty
                    if(!empty($data['counterparty_id'])){
                        $counterparty = $this->getBuilder(Counterparty::class, [
                            'id' => $data['counterparty_id']
                        ])->first();
                        if(!$counterparty)
                            throw new \Exception('Контрагент не найден');

                        $transaction->counterparty()->associate($counterparty);
                    }

                    // Create new counterparty [if not found]
                    elseif(!empty($data['counterparty_name']) && !empty($data['counterparty_inn'])){

                        $counterparty_data = [
                            'name' => Arr::get($data, 'counterparty_name'),
                            'inn' => Arr::get($data, 'counterparty_inn'),
                            'kpp' => Arr::get($data, 'counterparty_kpp'),
                            'legal_address' => Arr::get($data, 'counterparty_address'),
                        ];

                        $counterparty = $this->account->counterparties()->create($counterparty_data);
                        if(!$counterparty) throw new \Exception('Ошибка создания контрагента. Проверьте введенные данные');

                        if(!empty($data['counterparty_type'])){
                            $type = OPFType::find($data['counterparty_type']);
                            if($type) $counterparty->opf()->associate($type);
                        }

                        $transaction->counterparty()->associate($counterparty);
                    }


                    // Budget item
                    if(!empty($data['budget_item_id'])){
                        $budgetItem = $this->getBuilder(BudgetItem::class, [
                            'id' => $data['budget_item_id']
                        ])->first();
                        if(!$budgetItem)
                            throw new \Exception('Статья бюджета не найдена');

                        // type checking
                        if($budgetItem->category !== $data['type'])
                            throw new \Exception('Статья бюджета не может быть использована');

                        $transaction->budgetItem()->associate($budgetItem);
                    }


                    // Project
                    if(!empty($data['project_id'])){
                        $project = $this->getBuilder(Project::class, [
                            'id' => $data['project_id']
                        ])->first();
                        if(!$project)
                            throw new \Exception('Проект не найден');

                        $transaction->project()->associate($project);
                    }


                    // Save
                    if(!$transaction->save())
                        throw new \Exception('Не удалось сохранить транзакцию');


                /* STEP: Calculate chain balances
                -------------------------------------------*/
                    $now = Carbon::now();
                    $calculator = new Calculator();

                    $calculator->calculateTotalBalance($transaction);
                    $calculator->calculateAccountBalances($transaction);


                /* STEP: Made payment
                --------------------------------------------*/
                    if($date->getPreciseTimestamp(3) <= $now->getPreciseTimestamp(3))
                        $calculator->Core->madePayment($transaction);

            });

            return $this->success(compact('transaction'));
        }
        catch(ValidationException $exception){
            return $this->error(['errors' => $exception->errors()], $exception->getMessage());
        }
        catch(\Exception $exception){
            if(config('app.debug')){
                return $this->error([
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'code' => $exception->getCode(),
                    'trace' => $exception->getTrace()
                ], $exception->getMessage());
            }

            return $this->error($exception->getMessage());
        }
    }




    public function update(Request $request){

        try{
            $transaction = null;

            // open DB transaction
            DB::transaction(function() use ($request, $transaction){

                $Calc = new Calculator();

                /* STEP: Get Request Data and find transaction
                -------------------------------------------------*/
                    // Get transaction
                    $id = $request->route('id');
                    if(empty($id))
                        throw new \Exception('Транзакция не найдена');

                    $transaction = $this->account->transactions()->find($id);
                    if(!$transaction || !($transaction instanceof Transaction))
                        throw new \Exception('Транзакция не найдена');


                    // Get updates
                    $data = $this->getValidatedData();
                    if(!$data)
                        throw new \Exception($this->lastValidationError);

                    if($date = $data['date'])
                        $date = new Carbon($date);



                /* Collate changes for future recalculate
                --------------------------------------------*/

                    // amount changes
                    $oldAmount = $transaction->amount;
                    if(!empty($data['amount']) && floatval($data['amount']) !== $transaction->amount) {
                        $amount = $data['amount'];
                        unset($data['amount']);

                        $transaction->update(compact('amount'));

                        $Calc->processAmountChanges($transaction, $oldAmount);
                    }


                    // date changes
                    $oldDate = $transaction->date;
                    if($date && $date->getPreciseTimestamp(3) !== $transaction->date->getPreciseTimestamp(3)) {
                        unset($data['date']);
                        $transaction->update(compact('date'));
                        $Calc->processDateChanges($transaction, $oldDate);
                    }


                    // checking accounts changes
                    $accountChanges = false;
                    if(!empty($data['from_ca_id']) && $data['from_ca_id'] !== $transaction->from_ca_id)
                        $accountChanges = true;
                    if(!empty($data['to_ca_id']) && $data['to_ca_id'] !== $transaction->to_ca_id)
                        $accountChanges = true;

                    if($accountChanges) {
                        $Calc->rollbackCalculateBalances($transaction);
                        $Calc->Core->rollbackPayment($transaction);
                    }




                /* STEP: Update data
                --------------------------------------------*/
                    $transaction->update($data);



                /* STEP: Assign associates
                -------------------------------------------*/
                    // From checking account
                    if(!empty($data['from_ca_id'])){
                        $checkingAccount = $this->getBuilder(CheckingAccount::class, [
                            'id' => $data['from_ca_id']
                        ])->first();
                        if(!$checkingAccount)
                            throw new \Exception("Расчетный счет не найден");

                        $transaction->fromCheckingAccount()->associate($checkingAccount);
                    }
                    else
                        $transaction->fromCheckingAccount()->dissociate();

                    // To checking account
                    if(!empty($data['to_ca_id'])){
                        $checkingAccount = $this->getBuilder(CheckingAccount::class, [
                            'id' => $data['to_ca_id']
                        ])->first();
                        if(!$checkingAccount)
                            throw new \Exception("Расчетный счет не найден");

                        $transaction->toCheckingAccount()->associate($checkingAccount);
                    }
                    else
                        $transaction->toCheckingAccount()->dissociate();


                    // Counterparty
                    if(!empty($data['counterparty_id'])){
                        $counterparty = $this->getBuilder(Counterparty::class, [
                            'id' => $data['counterparty_id']
                        ])->first();
                        if(!$counterparty)
                            throw new \Exception('Контрагент не найден');

                        $transaction->counterparty()->associate($counterparty);
                    }

                    // Create new counterparty [if not found]
                    elseif(!empty($data['counterparty_name']) && !empty($data['counterparty_inn'])){

                        $counterparty_data = [
                            'name' => Arr::get($data, 'counterparty_name'),
                            'inn' => Arr::get($data, 'counterparty_inn'),
                            'kpp' => Arr::get($data, 'counterparty_kpp'),
                            'legal_address' => Arr::get($data, 'counterparty_address'),
                        ];

                        $counterparty = $this->account->counterparties()->create($counterparty_data);
                        if(!$counterparty) throw new \Exception('Ошибка создания контрагента. Проверьте введенные данные');

                        if(!empty($data['counterparty_type'])){
                            $type = OPFType::find($data['counterparty_type']);
                            if($type) $counterparty->opf()->associate($type);
                        }

                        $transaction->counterparty()->associate($counterparty);
                    }

                    else
                        $transaction->counterparty()->dissociate();


                    // Budget item
                    if(!empty($data['budget_item_id'])){
                        $budgetItem = $this->getBuilder(BudgetItem::class, [
                            'id' => $data['budget_item_id']
                        ])->first();
                        if(!$budgetItem)
                            throw new \Exception('Статья бюджета не найдена');


                        // type checking
                        if($budgetItem->category !== $data['type'])
                            throw new \Exception('Статья бюджета не может быть использована');

                        $transaction->budgetItem()->associate($budgetItem);
                    }
                    else
                        $transaction->budgetItem()->dissociate();


                    // Project
                    if(!empty($data['project_id'])){
                        $project = $this->getBuilder(Project::class, [
                            'id' => $data['project_id']
                        ])->first();
                        if(!$project)
                            throw new \Exception('Проект не найден');

                        $transaction->project()->associate($project);
                    }
                    else
                        $transaction->project()->dissociate();


                    // Save
                    if(!$transaction->save())
                        throw new \Exception('Не удалось сохранить транзакцию');


                /* STEP: Recalculate for account changes
                --------------------------------------------*/
                    if($accountChanges){
                        $Calc->calculateTotalBalance($transaction);
                        $Calc->calculateAccountBalances($transaction);
                    }


                /* STEP: Made payment
                --------------------------------------------*/
                    $now = new Carbon();
                    if($date->getPreciseTimestamp(3) <= $now->getPreciseTimestamp(3))
                        $Calc->Core->madePayment($transaction);

            });

            return $this->success(compact('transaction'));
        }
        catch(ValidationException $exception){
            return $this->error(['errors' => $exception->errors()], $exception->getMessage());
        }
        catch(\Exception $exception){
            if(config('app.debug')){
                return $this->error([
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'code' => $exception->getCode(),
                    'trace' => $exception->getTrace()
                ], $exception->getMessage());
            }

            return $this->error($exception->getMessage());
        }
    }



    public function remove(Request $request){
        $ids = $request->input('ids');
        if(empty($ids))
            return $this->error("Не указаны операции для удаления");

        $ids = explode(',', $ids);

        try{
            DB::transaction(function() use ($ids){

                // Get account transactions by ids
                $query = $this->getBuilder(Transaction::class)->whereIn('id', $ids);
                $transactions = $query->get();
                if(!$transactions->count())
                    return;

                $Calc = new Calculator();

                /** @var Transaction $transaction */
                foreach($transactions as $transaction){

                    // rollback transaction affect
                    $Calc->rollbackCalculateBalances($transaction);
                    $Calc->Core->rollbackPayment($transaction);
                    $transaction->delete(); // remove transaction
                }
            });
        }
        catch (\Exception $e){
            $this->error();
        }

        return $this->success();
    }



    protected $lastValidationError;
    protected function getValidatedData(){

        $request = $this->request;

        // Get type
        $type = $request->input('type');
        if(!$type || false === array_search($type, ['income', 'expense', 'transfer'])) {
            $this->lastValidationError = 'Неверный тип операции';
            return false;
        }

        // Get date
        $date = $request->input('date');
        $date = !empty($date) ? new Carbon($date) : '';
        if(!$date) {
            $this->lastValidationError = 'Укажите дату операции';
            return false;
        }

        // Get amount
        if(!($amount = $request->input('amount'))) {
            $this->lastValidationError = 'Укажите сумму';
            return false;
        }


        $data = $this->request->validate([
            'type' => 'in:income,expense,transfer',
            'amount' => 'required|numeric',
            'to_ca_id' => 'nullable|exists:App\Models\CheckingAccount,id',
            'from_ca_id' => 'nullable|exists:App\Models\CheckingAccount,id',
            'counterparty_id' => 'nullable|exists:App\Models\Counterparty,id',
            'project_id' => 'nullable|exists:App\Models\Project,id',
            'budget_item_id' => 'nullable|exists:App\Models\BudgetItem,id',
            'description' => 'nullable',

            // Counterparty
            'counterparty_name' => 'nullable',
            'counterparty_inn' => 'nullable',
            'counterparty_kpp' => 'nullable',
            'counterparty_address' => 'nullable',
            'counterparty_type' => 'nullable'
        ]);

        $data['date'] = $date;

        return $data;
    }


    /**
     * Получить коллекцию Transactions
     * - референсы
     * - пагинация
     * @param bool $with
     * @param bool $pagination
     * @param callable|null $filterHandler
     * @return false | Builder
     */
    protected function getModel($with = true, $pagination = true, callable $filterHandler = null){

        $model = $this->getBuilder(Transaction::class);

        if($with && $with = $this->request->get('with'))
            $model->with(explode(',', $with));

        if($filterHandler)
            call_user_func_array($filterHandler, [$model]);

        if($pagination && $ppc = $this->request->get('ppc', 15))
            $this->paginator = $model->paginate($ppc > 300 ? 300 : $ppc);

        return $model;
    }

    protected $paginator;







    protected function applyFilters(Builder $builder){

        /* STEP: Prepare
        -------------------------------------------*/
        $r = $this->request;
        $filters = $r->input('filters');
        if(empty($filters)) return;


        /* STEP: Apply allowed filters
        -------------------------------------------*/
        $allowedFilters = ['budget_item_id', 'checking_account_id', 'counterparty_id', 'project_id', 'type'];

        foreach($allowedFilters as $name){
            if(!empty($filters[$name]))
                $builder->where($name, $filters[$name]);
        }


        // date period
        if($filters['filterPeriod'] && count($filters['filterPeriod']) > 1){
            $periodFrom = Carbon::createFromTimestamp($filters['filterPeriod'][0]);
            $periodTo = Carbon::createFromTimestamp($filters['filterPeriod'][1]);
            $builder->where('date', '>=', $periodFrom->startOfDay())
                ->where('date', '<=', $periodTo->endOfDay());
        }

//        return $filters;
    }
}
