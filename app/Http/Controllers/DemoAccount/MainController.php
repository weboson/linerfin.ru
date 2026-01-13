<?php

namespace App\Http\Controllers\DemoAccount;

use App\Http\Controllers\Controller;
use App\Models\Account;
use App\Models\OPFType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MainController extends Controller
{

    public function __invoke(Request $request)
    {
        $user = $request->user();
        if(!$user->accounts()->count())
            $this->buildDemoAccount($user);

        return redirect(config('app.demo_account_link', 'https://demo.linerfin.ru'));
    }


    /**
     * Создать аккаунт с демо-данными
     * @param User $user
     */
    public function buildDemoAccount(User $user){
        $account = $user->accounts()->create([
            'subdomain' => "demo$user->id",
            'name' => 'Демо режим',
            'inn' => '563683310012931',
            'kpp' => '113991002000099',
            'ogrn' => '7739917857295932',
            'address' => 'г. Казань',
            'legal_address' => 'г. Казань',
            'director_position' => 'Главный директор',
            'director_name' => implode(' ', [$user->surname, $user->name, $user->patronymic]),
            'is_demo' => true
        ]);

        if( !($account instanceof Account) )
            return;

        $account->opf()->associate(OPFType::find(1));

        $this->runBuilding($user, $account);
    }



    protected $handlers = [
        BudgetItemBuilder::class,
        ProjectBuilder::class,
        CheckingAccountBuilder::class,
        TransactionBuilder::class,
        CounterpartyBuilder::class,
        BillBuilder::class
    ];


    protected function runBuilding(User $user, Account $account){
        foreach($this->handlers as $handler){
            /** @var BuilderAbstract $handler */
            $handlerInstance = app()->make($handler, compact('user', 'account'));
            $handlerInstance->build();
        }
    }


}
