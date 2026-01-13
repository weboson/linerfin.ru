<?php

namespace App\Http\Controllers\UI\Transactions;

use App\Http\Abstraction\AccountAbstract;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class GraphController extends AccountAbstract
{


    /**
     * Получить данные для построения графика
     * Get graph data
     * ------------------------
     * @param Request $request
     * @return \App\Http\Responses\JsonResponse
     */
    public function getGraph(Request $request){
        $builder = $this->getBuilder(Transaction::class);
        $builder->select(['id', 'amount', 'type', 'made_at', 'total_balance', 'date'])
            ->orderBy('date');

        // format dates
        if($from = $request->get('period-from')) {
            $from = Carbon::createFromTimestamp($from / 1000);
            $fromTimestamp = $from->toDateTimeString();
            $builder->where('date', '>=', $fromTimestamp);
        }
        if($to = $request->get('period-to')) {
            $to = Carbon::createFromTimestamp($to / 1000);
            $toTimestamp = $to->toDateTimeString();
            $builder->where('date', '<=', $toTimestamp);
        }

        $transactions = $builder->get();

        // Normalize graph
        $normalized = [];
        foreach($transactions as $transaction){
            $date = new Carbon($transaction['date']);
            $normalized[$date->format('dmY')] = $transaction;
        }
        $transactions = array_values($normalized);


        // get start & end points
        if(count($transactions)){
            $TCalc = new Calculator();

            // get start point
            if(!empty($fromTimestamp)){
                $firstTransaction = $transactions[0];
                $balance = $firstTransaction->total_balance;
                if($firstTransaction->type !== 'transfer'){
                    $amount = $firstTransaction->amount;
                    if($firstTransaction->type === 'income')
                        $amount *= -1;
                    $balance += $amount;
                }
                array_unshift($transactions, [
                    "total_balance" => $balance,
                    "date" => $fromTimestamp
                ]);
            }

            // get end point
            if(!empty($toTimestamp)){
                $now = new Carbon();
                $now->endOfDay();
//                throw new \Exception($now->toDateTimeString());

                $endPointDate = ($to->getPreciseTimestamp(3) < $now->getPreciseTimestamp(3)) ? $to : $now;
                $lastTransaction = end($transactions);

                if($lastTransaction->date->getPreciseTimestamp(3) < $endPointDate->getPreciseTimestamp(3)){
                    if($lastTransaction)
                        $balance = $lastTransaction->total_balance;
                    else
                        $balance = $TCalc->getLastTotalBalanceByDate($endPointDate);

                    array_push($transactions, [
                        "total_balance" => $balance,
                        "date" => $endPointDate->toDateTimeString()
                    ]);
                }
            }
        }

        return $this->success(compact('transactions'));
    }


    /**
     * Получить данные для графика тип Pie
     * Get graph pie data
     * ----------------------------
     * @param Request $request
     * @return \App\Http\Responses\JsonResponse
     */
    public function getGraphPie(Request $request){
        $category = $request->route('category');
        if(!$category || false === array_search($category, ['budget-items', 'projects', 'bills']))
            return $this->success();

        $account = $this->account;

        switch ($category){
            case 'budget-items':
                $expenses = $this->getPieGraphBuilder('budgetItem', 'budget_item_id', 'expense')->get();
                $expensesLabels = $this->getPieGraphLabels($expenses, 'budgetItem.name', 'Без статьи');
                $incomes = $this->getPieGraphBuilder('budgetItem', 'budget_item_id', 'income')->get();
                $incomesLabels = $this->getPieGraphLabels($incomes, 'budgetItem.name', 'Без статьи');
                break;

            case 'projects':
                $expenses = $this->getPieGraphBuilder('project', 'project_id', 'expense')->get();
                $expensesLabels = $this->getPieGraphLabels($expenses, 'project.name', 'Без проекта');
                $incomes = $this->getPieGraphBuilder('project', 'project_id', 'income')->get();
                $incomesLabels = $this->getPieGraphLabels($incomes, 'project.name', 'Без проекта');
                break;

            case 'bills': // issued | rejected | paid
                $labels = [
                    'income' => ['Оплачены', 'Не оплачены', 'Отменены']
                ];
                $series = [
                    'income' => [
                        $this->account->bills()->where('status', 'paid')->groupBy('status')->sum('sum'),
                        $this->account->bills()->where('status', 'issued')->groupBy('status')->sum('sum'),
                        $this->account->bills()->where('status', 'rejected')->groupBy('status')->sum('sum'),
                    ]
                ];
                return $this->success(compact('series', 'labels'));
        }

        $series = [];
        if(!empty($expenses))
            $series['expense'] = Arr::pluck($expenses->toArray(), 'amount');
        if(!empty($incomes))
            $series['income'] = Arr::pluck($incomes->toArray(), 'amount');

        $labels = [];
        if(!empty($expensesLabels))
            $labels['expense'] = $expensesLabels;
        if(!empty($incomesLabels))
            $labels['income'] = $incomesLabels;

        return $this->success(compact('series', 'labels'));
    }


    protected function getPieGraphBuilder(string $with, string $groupBy, string $type = null){
        $builder = $this->account->transactions()->select([$groupBy, DB::raw('sum(amount) as amount')])->with($with)->groupBy($groupBy);
        if($type) $builder->where('type', $type);
        if($from = $this->request->get('period-from'))
            $builder->where('date', '>=', Carbon::createFromTimestamp($from/1000)->toDateTimeString());
        if($to = $this->request->get('period-to'))
            $builder->where('date', '<=', Carbon::createFromTimestamp($to/1000)->toDateTimeString());
        return $builder;
    }

    protected function getPieGraphLabels(Collection $items, string $relation, string $default = ''){
        $labels = [];
        list($relation, $prop) = explode('.', $relation);
        if(empty($prop))
            return [];

        foreach($items as $item) {
            $relatedItem = $item->$relation;
            if ($relatedItem && $relatedItem->$prop)
                $labels[] = $item->$relation->$prop;
            else
                $labels[] = $default;
        }

        return $labels;
    }
}
