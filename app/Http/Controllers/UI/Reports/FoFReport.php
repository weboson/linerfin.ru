<?php

namespace App\Http\Controllers\UI\Reports;

use App\Http\Controllers\UI\Transactions\Calculator;
use App\Models\BudgetItemType;
use App\Models\Project;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class FoFReport extends PLReport
{

    // Get data for report
    public function getData(Request $request){
        // income params
        // > period type
        // > year
        // > half-year (for months)
        // > report type

        $validator = Validator::make($_REQUEST, [
            'period_type' => 'nullable|in:month,quarter',
            'year' => 'nullable|numeric',       // for quarters
            'half_year' => 'nullable|numeric',  // for months
//            'report_type' => 'nullable|in:budget_items,projects'
        ]);

        if($validator->failed())
            return $this->error(['errors' => $validator->errors()], 'Ошибка при получении данных');

//        $reportType = $request->input('report_type', 'budget_items');
        $periods = $this->getPeriods($request);
        $data = $this->buildData($periods);

        /*
         * $data structure:
         * [
         *      {
         *          type: 'header', // row type
         *          data: [...] // data by periods
         *      },
         *      ...
         * ]
         */

        return $this->success(compact('periods', 'data'));
    }

    protected function buildData($periods){

        $data = [];

        // 1. by budget item types [operation | investment | financial]
        $activityTypes = [
            'operation' => 'Операционные',
            'investment' => 'Инвестиционные',
            'financial' => 'Финансовые'
        ];
        foreach($activityTypes as $activityType => $activityTitle){

            $categoryData = []; // by activity type
            $processedItems = []; // Для отслеживания уже обработанных статей бюджета

            foreach(['income', 'expense'] as $operation_category){
                /** @var BudgetItemType $budgetItemsTypes */
                $budgetItemsTypes = BudgetItemType::where('type', $activityType)
                    ->where('category', $operation_category)->with('budget_items')->get();

                $budgetItems = $budgetItemsTypes->pluck('budget_items');
                $budgetItems = self::test1($budgetItems->toArray());

                foreach($budgetItems as $budgetItem){ // compare data by budget items

                    // Создаем уникальный ключ для статьи бюджета
                    $itemKey = $budgetItem['id'] . '_' . $activityType;
                    
                    // Проверяем, не обрабатывали ли уже эту статью бюджета
                    if(isset($processedItems[$itemKey])) {
                        continue; // Пропускаем дубликат
                    }
                    
                    // Помечаем статью как обработанную
                    $processedItems[$itemKey] = true;

                    $dataByPeriods = [];

                    foreach($periods as $period){ // compare data by periods
                        list($from, $to) = $period;

                        $query = $this->getBuilder(Transaction::class, [
                            'budget_item_id' => $budgetItem['id'],
                            'type' => $operation_category,
                        ]);

                        $query->where('date', '>=', $from);
                        $query->where('date', '<=', $to);

                        $sum = $query->sum('amount');
                        if($operation_category === 'expense')
                            $sum *= -1;

                        $dataByPeriods[] = $sum;

                    } // <- period cycle

                    $categoryData[] = [
                        'name' => $budgetItem['name'],
                        'data' => $dataByPeriods
                    ];

                } // <- budget types cycle

            } // <- operation type

            $categorySumByPeriods = $this->sumCategoryByPeriods($categoryData);

            array_unshift($categoryData, [
                'type' => 'header',
                'name' => $activityTitle,
                'data' => $categorySumByPeriods
            ]);

            $data = array_merge($data, $categoryData);
        } // <- activity types cycle


        // 2. balance on start and end period
        $balanceByPeriod = $this->getTotalBalanceByPeriod($periods);
        if($balanceByPeriod) {
            if(!empty($balanceByPeriod['start'])){
                array_unshift($data, [
                    'name' => 'На начало периода',
                    'type' => 'header balance',
                    'data' => $balanceByPeriod['start']
                ]);
            }
            if(!empty($balanceByPeriod['diff'])){
                array_push($data, [
                    'name' => 'Разница',
                    'type' => 'header',
                    'data' => $balanceByPeriod['diff']
                ]);
            }
            if(!empty($balanceByPeriod['end'])){
                array_push($data, [
                    'name' => 'На конец периода',
                    'type' => 'header balance',
                    'data' => $balanceByPeriod['end']
                ]);
            }
        }

        return $data;
    }


    protected function getTotalBalanceByPeriod($periods){

        $Calc = new Calculator();
        $startPeriodSum = [];
        $endPeriodSum = [];
        $diffPeriodSum = [];

        foreach($periods as $period){
            list($from, $to) = $period;
            $start = $Calc->Core->getLastTotalBalanceByDate(new Carbon($from));
            $end = $Calc->Core->getLastTotalBalanceByDate(new Carbon($to));
            $diff = $end - $start;

            $startPeriodSum[] = $start;
            $endPeriodSum[] = $end;
            $diffPeriodSum[] = $diff;
        }

        return [
            'start' => $startPeriodSum,
            'diff' => $diffPeriodSum,
            'end' => $endPeriodSum
        ];
    }


    protected function sumCategoryByPeriods($categoryData){

        $sumByPeriods = [];

        foreach($categoryData as $categoryRow){
            if(empty($categoryRow['data']))
                continue;
            foreach ($categoryRow['data'] as $i => $sum){
                if(!isset($sumByPeriods[$i]))
                    $sumByPeriods[$i] = 0;
                $sumByPeriods[$i] += $sum;
            }
        }

        return $sumByPeriods;
    }


    protected static function test1($collections){
        $data = [];
        foreach($collections as $collection)
            $data = array_merge($data, $collection);

        return $data;
    }


    // Get periods for report filtering
    protected function getPeriods(Request $request){
        $periodType = $request->input('period_type', 'month');
        $year = $request->input('year', date('Y'));
        $currentMonth = date('m');
        $halfYear = $request->input('half_year', $currentMonth < 6 ? 1 : 2);

        $periods = [];


        switch ($periodType){
            case 'month':
                for($i = ($halfYear == 2 ? 7 : 1); $i <= 6 * $halfYear; $i++) {
                    $date = new Carbon("01.$i.$year");
                    $periods[] = [
                        $date->toDateTimeString(), $date->endOfMonth()->toDateTimeString()
                    ];
                }
                break;
            case 'quarter':
                foreach([1,4,7,10] as $startMonth){
                    $date = new Carbon("01.$startMonth.$year");
                    $periods[] = [
                        $date->startOfQuarter()->toDateTimeString(),
                        $date->endOfQuarter()->toDateTimeString(),
                    ];
                }
                break;
            default:
                return [];
        }

        return $periods;
    }



    protected function buildProceeds(array $CarbonPeriods, string $reportType){
        $proceedsData = [];

        foreach($CarbonPeriods as $period){
            $builder = Transaction::where('account_id', $this->account_id)
                ->where('type', 'income')
                ->where('date', '>=', $period[0])
                ->where('date', '<=', $period[1]);

            if($reportType == 'projects')
                $builder->whereNotNull('project_id');
            else
                $builder->whereNotNull('budget_item_id');

            $proceedsData[] = $builder->sum('amount');
        }

        return $proceedsData;
    }

    protected function buildExpenses(array $CarbonPeriods, string $reportType){
        $ExpensesStack = [];
        /*
         * one expense
         * [
         *    name => {name}
         *    data => [123234, 345642, 534543, ..]
         * ]
         */

        $isProjects = ($reportType === 'projects');

        // get builder
        $exBuilder = $isProjects ? $this->account->projects() : $this->account->budgetItems();
        $exBuilder->where('archived', false);

        $exItems = $exBuilder->get(); // <- projects or budget items

        foreach($exItems as $exItem){
            $exItemID = $exItem->id;
            $expenseName = $exItem->name;
            $expenseData = [];
            foreach($CarbonPeriods as $period){
                $trBuilder = Transaction::where('account_id', $this->account_id)
                    ->where('type', 'expense')
                    ->where('date', '>=', $period[0])
                    ->where('date', '<=', $period[1]);

                if($isProjects)
                    $trBuilder->where('project_id', $exItemID);
                else
                    $trBuilder->where('budget_item_id', $exItemID);

                $expenseData[] = $trBuilder->sum('amount') * -1;
            }
            $ExpensesStack[] = ["name" => $expenseName, "data" => $expenseData];
        } // <- end $exItems cycle

        return $ExpensesStack;
    }
}