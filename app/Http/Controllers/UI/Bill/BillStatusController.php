<?php


namespace App\Http\Controllers\UI\Bill;


use App\Exceptions\AccountAuthorizeException;
use App\Http\Abstraction\AccountAbstract;
use App\Http\Controllers\UI\Transactions\Calculator;
use App\Models\Bill;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillStatusController extends AccountAbstract
{

    /**
     * Initialize
     * tags: [init, construct, validate]
     * @throws AccountAuthorizeException
     */
    public function authorize_account()
    {
        if(!parent::authorize_account())
            return false;


        // Get bill
        if($bill_id = $this->request->route('id'))
            $bill = $this->getBuilder(Bill::class, ['id' => $bill_id])->first();

        if(empty($bill_id) || empty($bill))
            throw new AccountAuthorizeException('Счет не найден');

        // Get status
        $status = $this->request->input('status');
        if(!$status || false === array_search($status, ['paid', 'rejected', 'issued', 'realized', 'part-paid']))
            throw new AccountAuthorizeException('Неверный статус счёта');

        $this->bill = $bill;
        $this->bill_id = $bill_id;
        $this->requestStatus = $status;

        return true;
    }


    // controller properties
    protected $bill;
    protected $bill_id;
    protected $requestStatus;




    /**
     * Установить статус
     * tags: [router, main]
     * @return \App\Http\Responses\JsonResponse
     * @throws \Exception
     */
    public function setStatus(){

        $bill = $this->bill;



        switch ($this->requestStatus) {

            // SECTION >>

            case 'part-paid':
                // for part-paid get and check sum from POST input

                $sum = request()->input('sum');
                if (!$sum)
                    return $this->error('Укажите сумму оплаты');

            case 'paid':
                // for paid get sum from factSum - billSum

                $factSum = $bill->transactions()->sum('amount');
                $billSum = $bill->sum;

                // part-paid continuation
                if (!empty($sum)) {
                    if ($sum > ($billSum - $factSum))
                        return $this->error("Указанная сумма превышает сумму счёта");
                } else {
                    $sum = $billSum - $factSum;
                }

                // create transaction
                if (!empty($sum)) {
                    $transaction = $this->createTransaction($sum);
                    if (!$transaction)
                        return $this->error('Не удалось обновить статус счёта');
                }

                // update bill status
                $factSum = $bill->transactions()->sum('amount');
                if ($billSum === $factSum) {
                    $bill->paid_at = new Carbon($this->request->input('paid_at'));
                    if (!$bill->issued_at)
                        $bill->issued_at = new Carbon($this->request->input('issued_at'));
                }

                break;

            // << END SECTION


            case 'rejected':

                // get transactions and reject this
                $transactions = $bill->transactions;
                $Calc = new Calculator();
                try {
                    DB::transaction(function() use ($transactions, $Calc){
                        /** @var Transaction $transaction */
                        foreach($transactions as $transaction){
                            $Calc->rollbackCalculateBalances($transaction);
                            $Calc->Core->rollbackPayment($transaction);
                            $transaction->delete();
                        }
                    });
                } catch (\Exception $exception){
                    $this->error("Не удалось отменить счёт");
                }

                $bill->rejected_at = new Carbon($this->request->input('rejected_at'));
                $bill->paid_at = $bill->issued_at = $bill->realized_at = null;
                break;
            case 'issued':
                $bill->status = 'issued';
                $bill->issued_at = new Carbon($this->request->input('issued_at'));
                $bill->paid_at = $bill->rejected_at = $bill->realized_at = null;
                break;
            case 'realized':
                $bill->realized_at = new Carbon($this->request->input('realized_at'));
                $bill->rejected_at = null;
                break;
        }

        $bill->save();

        // load relations
        if($with = $this->requestWith())
            $bill->load($with);


        return $this->success(compact('bill'));
    }


    /**
     * Create related transaction
     * Создать связанную транзакцию
     * tags: [secondary]
     * @param float $sum
     * @return Transaction
     */
    protected function createTransaction(float $sum){

        try{
            $bill = $this->bill;

            // STEP: create transaction
            $transaction = new Transaction([
                'amount' => $sum, // amount
                'description' => $bill->comment, // comment
                'type' => 'income', // type :income, expense
                'date' => new Carbon(),// date
            ]);

            // Relations
            $transaction->account()->associate($this->account);
            $transaction->bill()->associate($bill);
            $transaction->toCheckingAccount()->associate($bill->checking_account);
            $transaction->counterparty()->associate($bill->counterparty);
            $transaction->nds_type()->associate($bill->nds_type);

            DB::transaction(function() use ($bill, $sum, $transaction){

                // Save
                $transaction->save();

                // Made payment
                $Calc = new Calculator();
                $Calc->Core->madePayment($transaction);

                // Calculate
                $Calc->calculateTotalBalance($transaction);
                $Calc->calculateAccountBalances($transaction);
            });
        }
        catch(\Exception $exception){
            /*if(config('app.debug')){
                $this->error([
                    'file' => $exception->getFile(),
                    'line' => $exception->getLine(),
                    'code' => $exception->getCode(),
                    'trace' => $exception->getTrace()
                ], $exception->getMessage());

                return false;
            }*/
            echo $exception->getMessage();
            dd($exception);
        }

        return $transaction;
    }




    public function removeTransaction(){

    }




    // -------------------------------

    protected $allowedWith = [
        'account',
        'positions',
        'positions.nds_type',
        'checking_account',
        'template',
        'counterparty',
        'signature_list',
        'signature_list.signature_attachment',
        'signature_list_with_attachments',
        'stamp_attachment',
        'logo_attachment',
        'transactions',
    ];

    protected function requestWith($with = null){
        $with = request()->input('with');
        return parent::requestWith($with);
    }
}
