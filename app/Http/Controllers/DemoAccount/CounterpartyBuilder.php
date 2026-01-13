<?php


namespace App\Http\Controllers\DemoAccount;


use Faker\Factory;
use Faker\Provider\ru_RU\Company;
use Faker\Provider\ru_RU\Address;

class CounterpartyBuilder extends BuilderAbstract
{

    public function build()
    {
        $faker = Factory::create('ru_RU');
        $faker->addProvider(new Company($faker));
        $faker->addProvider(new Address($faker));
        $counterpartyData = [];

        for($i = 0; $i < 20; $i++){
            $counterpartyData[] = [
                'name' => $faker->company(),
                'inn' => $faker->inn(),
                'kpp' => $faker->kpp(),
                'address' => $faker->address()
            ];
        }

        $this->account->counterparties()->createMany($counterpartyData);
    }
}
