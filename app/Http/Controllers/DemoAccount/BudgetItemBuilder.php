<?php


namespace App\Http\Controllers\DemoAccount;


use App\Models\BudgetItem;
use App\Models\BudgetItemType;
use Illuminate\Support\Arr;

class BudgetItemBuilder extends BuilderAbstract
{
    public function build()
    {
        $this->createIncomeItems();
        $this->createExpenseItems();
    }

    protected function createIncomeItems(){
        $items = [
            ['name' => 'Выручка', 'type_id' => '8'],
            ['name' => 'Пополнение счета', 'type_id' => '9'],
            ['name' => 'Кредит', 'type_id' => '11'],
        ];

        $this->createItems($items);
    }

    protected function createExpenseItems(){
        $items = [
            ['name' => 'На основную деятельность', 'type_id' => '1'],
            ['name' => 'Себестоимость', 'type_id' => '7'],
            ['name' => 'Налоги', 'type_id' => '6'],
            ['name' => 'Кредит', 'type_id' => '4'],
            ['name' => 'Вывод прибыли', 'type_id' => '2'],
        ];

        $this->createItems($items);
    }

    protected function createItems(array $items){
        foreach($items as $item){
            $budgetItem = new BudgetItem($item);

            $type_id = Arr::get($item, 'type_id');
            if($type_id && $type = BudgetItemType::find($type_id))
                $budgetItem->type()->associate($type);

            $budgetItem->account()->associate($this->account);

            $budgetItem->save();
        }
    }
}
