<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Bank;
use App\Models\BudgetItemType;
use App\Models\CheckingAccount;
use App\Models\Contact;
use App\Models\Counterparty;
use App\Models\CounterpartyCategory;
use App\Models\CounterpartyType;
use App\Models\OPFType;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        // Create user
        $user = new User([
            'name' => 'Алексей',
            'surname' => 'Андреев',
            'patronymic' => 'Викторович',
            'email' => "user@mail.io",
            'email_verified_at' => now(),
            'password' => Hash::make('secretpwd')
        ]);

        $user->save();


        // Create NDS types
        DB::table('nds_types')->insert([
            ['name' => 'Без НДС', 'percentage' => null],
            ['name' => '0%', 'percentage' => 0],
            ['name' => '10%', 'percentage' => 10],
            ['name' => '20%', 'percentage' => 20]
        ]);

        // Create Taxations
        DB::table('taxation_systems')->insert([
            ['name' => 'Основная система налогообложения'],
            ['name' => 'Упрощенная "Доход"'],
            ['name' => 'Упрощенная "Доход минус расход"'],
        ]);

        // Create organization types
        $OOO = OPFType::create([
            'name' => 'Общество с ограниченной ответственностью',
            'short_name' => 'ООО',
            'code' => 12300,
            'for_legal' => true
        ]);

        $OPFTypes = [
            ['name' => 'Индивидуальный предприниматель', 'short_name' => 'ИП', 'code' => 50102, 'for_individual' => true],
            ['name' => 'Публичное акционерное общество', 'short_name' => 'ПАО', 'code' => 12247, 'for_legal' => true],
            ['name' => 'Открытое акционерное общество', 'short_name' => 'ОАО', 'for_legal' => true],
            ['name' => 'Самозанятый', 'short_name' => 'Самозанятый', 'for_individual' => true],
            ['name' => 'Государственная корпорация', 'short_name' => 'ГК', 'code' => 71601, 'for_legal' => true],
            ['name' => "Непубличное акционерное общество", 'short_name' => 'НАО', 'code' => 12267, 'for_legal' => true],
            ['name' => "Некоммерческая организация", 'short_name' => 'НО', 'code' => null, 'for_legal' => true],
        ];

        foreach($OPFTypes as $OPFType)
            OPFType::create($OPFType);


        // Create budget item types
        $BITypes = [
            // expense
            ['name' => 'на основную деятельность', 'type' => 'operation', 'category' => 'expense'],
            ['name' => 'вывод прибыли и дивиденты', 'type' => 'operation', 'category' => 'expense'],
            ['name' => 'на основные средства, капитальные вложения', 'type' => 'investment', 'category' => 'expense'],
            ['name' => 'выплата кредита, займа или возвратного депозита', 'type' => 'financial', 'category' => 'expense'],
            ['name' => 'погашение процентов', 'type' => 'operation', 'category' => 'expense'],
            ['name' => 'оплата налогов', 'type' => 'operation', 'category' => 'expense'],
            ['name' => 'себестоимость', 'type' => 'cost', 'category' => 'expense'],

            // income
            ['name' => 'от основной деятельности', 'type' => 'operation', 'category' => 'income'],
            ['name' => 'ввод капитала', 'type' => 'operation', 'category' => 'income'],
            ['name' => 'от продажи основных средств', 'type' => 'investment', 'category' => 'income'],
            ['name' => 'получение кредита, займа или возвратного депозита', 'type' => 'financial', 'category' => 'income'],
        ];

        foreach ($BITypes as $BIType)
            BudgetItemType::create($BIType);



        // Create organization
        $account = new Account([
            'subdomain' => 'financial',
            'name' => "ООО Финансы",
            'inn' => '58392239258293234',
            'kpp' => '358202384210',
            'ogrn' => '2001813301123',
            'address' => 'Екатеринодар, ул. Дореволюционная, 17',
        ]);

        $account->opf()->associate($OOO);
        $user->accounts()->save($account);


        // Create counterparty category
        $account->counterpartyCategories()->createMany([
            ['name' => 'Партнеры'],
            ['name' => 'Клиенты'],
        ]);

        $counterparty = new Counterparty([
            'name' => 'ООО Хорошие Ворота',
            'inn' => '333 44444 3333 55',
            'ogrn' => '58949392 2939292',
            'kpp' => '349917247283',
            'address' => "г. Казань, ул. Татарстана, 15/4, офис 500",
        ]);
        $counterpartyCategory = CounterpartyCategory::whereAccountId($account->id)->first();
        $counterparty->category()->associate($counterpartyCategory);
        $counterparty->opf()->associate($OOO);
        $account->counterparties()->save($counterparty);

        $counterparty = new Counterparty([
            'name' => 'ООО ШинПромСтрой',
            'inn' => '482 8812 81238 12',
            'ogrn' => '58293923 2343343',
            'kpp' => '0111922184578',
            'legal_address' => "г. Краснодар, ул. Кубанская, 8",
        ]);
        $counterparty->category()->associate($counterpartyCategory);
        $counterparty->opf()->associate($OOO);
        $account->counterparties()->save($counterparty);


        $counterparty = new Counterparty([
            'name' => 'ООО Неплохие двери',
            'inn' => '333 44444 3333 55',
            'ogrn' => '333 44444 3333 55',
            'kpp' => '333 44444 3333 55',
            'address' => "г. Казань, ул. Татарстана, 15/4, офис 500",
        ]);
        $counterparty->category()->associate($counterpartyCategory);
        $counterparty->opf()->associate($OOO);
        $account->counterparties()->save($counterparty);


        // Create contacts
        $contact = new Contact([
            'name' => 'Иван',
            'surname' => 'Иванов',
            'patronymic' => 'Иванович',
            'phone' => '+799912312322',
            'email' => 'ivan@mail.ru',
            'main_contact' => true,
        ]);
        $contact->counterparty()->associate($counterparty);
        $contact->account()->associate($account);
        $contact->save();





        // Create projects
        $projects[] = DB::table('projects')->insertGetId([
            'account_id' => $account->id,
            'name' => 'Web Design'
        ]);

        $projects[] = DB::table('projects')->insertGetId([
            'account_id' => $account->id,
            'name' => 'Мой проект'
        ]);

        $projects[] = DB::table('projects')->insertGetId([
            'account_id' => $account->id,
            'name' => 'Backend Dev'
        ]);




        // Create checking accounts
        $ca1_id = DB::table('checking_accounts')->insertGetId([
            'account_id' => $account->id,
            'name' => 'ПАО Сбербанк',
            'num' => '1422 8382 1829 3728',
            'bank_name' => 'ПАО Сбербанк',
            'bank_bik' => '564564564565',
            'balance' => 10000
        ]);

    }
}
