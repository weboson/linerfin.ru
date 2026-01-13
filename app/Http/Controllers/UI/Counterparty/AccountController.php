<?php


namespace App\Http\Controllers\UI\Counterparty;


use App\Http\Abstraction\AccountAbstract;
use App\Http\Traits\JsonResponses;
use App\Models\Bank;
use App\Models\Contact;
use App\Models\Counterparty;
use App\Models\CounterpartyAccount;
use App\Models\CounterpartyAccount as Account;
use Illuminate\Database\Eloquent\Model;

class AccountController extends AccountAbstract
{
    protected $counterparty;
    protected $counterparty_account;

    const ValidateRules = [
        'checking_num' => 'required|max:40',
        'bank_id' => 'nullable|exists:App\Models\Bank,id'
    ];



    public function __construct(){
        parent::__construct();

        $request = app()->make('request');
        $counterparty_id = $request->route('counterparty_id');
        if($counterparty_id)
            $this->counterparty = Counterparty::find($counterparty_id);

        $account_id = $request->route('id');
        if($account_id)
            $this->counterparty_account = Account::find($account_id);
    }


    public function accounts(){
        $accounts = [];
        if(is_a($this->counterparty, Counterparty::class))
            $accounts = $this->counterparty->accounts;

        return $this->success(compact('accounts'));
    }


    public function create(){

        $data = $this->validated_request();

        // Create account model
        $account = new Account();
        $account->checking_num = $data['checking_num'];

        // Get bank
        if(!empty($data['bank_id'])){
            $bank = Bank::find($data['bank_id']);
            $account->bank()->associate($bank);
        }

        // Save
        $account->counterparty()->associate($this->counterparty);
        $account->account()->associate($this->account);

        if(!$account->save())
            return $this->error([], 'Не удалось создать счет');

        return $this->success(compact('account'));
    }

    public function update(){

        if(!is_a($this->counterparty_account, CounterpartyAccount::class))
            return $this->error([], 'Счет не найден');

        $account = $this->counterparty_account;
        $data = $this->validated_request(['checking_num' => 'max:40']);

        if(!empty($data['checking_num']))
            $account->checking_num = $data['checking_num'];

        // Get bank
        if(!empty($data['bank_id'])){
            $bank = Bank::find($data['bank_id']);
            $account->bank()->associate($bank);
        }

        if(!$account->save())
            return $this->error([], 'Не удалось обновить счет');

        return $this->success(compact('account'));
    }

    public function delete(){

        if(!is_a($this->counterparty_account, CounterpartyAccount::class))
            return $this->error([], 'Счет не найден');

        if(!$this->counterparty_account->delete())
            return $this->error([], 'Не удалось удалить контакт');

        return $this->success();
    }



    protected function validated_request(Array $rules = []){

        $rules = array_merge([
            'checking_num' => 'required|max:40',
            'bank_id' => 'nullable|exists:App\Models\Bank,id'
        ], $rules);

        return $this->request->validate($rules);
    }

}
