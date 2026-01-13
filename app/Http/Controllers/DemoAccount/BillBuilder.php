<?php


namespace App\Http\Controllers\DemoAccount;


use App\Models\Bill;
use Carbon\Carbon;
use Faker\Factory;
use Faker\Provider\Person;

class BillBuilder extends BuilderAbstract
{

    protected $faker;

    public function build()
    {
        $this->faker = Factory::create('en_US');
        $this->faker->addProvider(new Person($this->faker));
        for($i = 0; $i < 30; $i++)
            $this->generateBill($i);

    }


    protected function generateBill($index = 0){
        $bill = new Bill([
            'issued_at' => (new Carbon())->format('Y-m-d')
        ]);

        $bill->account()->associate($this->account);

        // get counterparty
        $counterparty  = $this->account->counterparties()->inRandomOrder()->first();
        $bill->counterparty()->associate($counterparty);

        // get checking account
        $chAccount = $this->account->checkingAccounts()->where('name', '!=', 'Наличные')->inRandomOrder()->first();
        if($chAccount)
            $bill->checking_account()->associate($chAccount);

        $bill->save();

        $this->generatePositions($bill, mt_rand(2, 7));
    }

    protected function generatePositions(Bill $bill, int $count){
        $positions = [];
        $billSum = 0;

        for($i = 0; $i < $count; $i++){
            $unitPrice = mt_rand(7, 4000);
            $count = mt_rand(1, 4);
            $billSum += $unitPrice * $count;
            $positions[] = [
                'account_id' => $this->account->id,
                'name' => $this->faker->firstName(),
                'unit_price' => $unitPrice,
                'count' => $count,
                'units' => 'шт'
            ];
        }

        $bill->positions()->createMany($positions);
        $bill->update(['sum' => $billSum]);
    }
}
