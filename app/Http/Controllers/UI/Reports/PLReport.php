<?php

namespace App\Http\Controllers\UI\Reports;

use App\Models\Project;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class PLReport extends \App\Http\Abstraction\AccountAbstract
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
            'report_type' => 'nullable|in:budget_items,projects'
        ]);

        if($validator->failed())
            return $this->error(['errors' => $validator->errors()], 'Ошибка при получении данных');

        $reportType = $request->input('report_type', 'budget_items');
        $periods = $this->getPeriods($request);

        $proceeds = $this->buildProceeds($periods, $reportType);
        $expenses = $this->buildExpenses($periods, $reportType);

        return $this->success(compact('periods', 'proceeds', 'expenses'));
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
