<?php


namespace App\Http\Controllers\UI\Settings;


use App\Models\CheckingAccount;
use Illuminate\Http\Request;

class Banks extends \App\Http\Abstraction\AccountAbstract
{

    public function getList(){
        $banks = $this->account->checkingAccounts();
        return $this->success(compact('banks'));
    }

    public function save(Request $request){

        // Validate request
        $validated = $request->validate([
            'name' => 'required|max:150',
            'num' => 'required|max:150',
            'balance' => 'nullable|numeric',
            'bank_name' => 'nullable|string|max:120',
            'bank_bik' => 'nullable|string|max:120',
            'bank_swift' => 'nullable|string|max:120',
            'bank_inn' => 'nullable|string|max:120',
            'bank_kpp' => 'nullable|string|max:120',
            'bank_correspondent' => 'nullable|string|max:120',
            'comment' => 'nullable|string|max:300'
        ]);


        // Get bank
        $bank_id = $request->route('id');
        if($bank_id){
            $bank = $this->account->checkingAccounts()->find($bank_id);
            if(!$bank)
                return $this->error('Расчётный счёт не найден');
        }

        if(!empty($bank)){
            unset($validated['balance']);
            $bank->update($validated);
        }
        else{
            $bank = $this->account->checkingAccounts()->create($validated);
            $bank->save();
        }

        return $this->success(compact('bank'));
    }



    public function remove(Request $request){
        if($bank_ids = $request->input('ids')){
            $bank_ids = explode(',', $bank_ids);

            $banks = $this->account->checkingAccounts()->whereIn('id', $bank_ids);
            if($banks->count()){
                $banks->delete();
                return $this->success();
            }
        }
        return $this->error('Расчётный счёт не найден');
    }



    public function setBalance(Request $request){
        $balance = (float) $request->input('balance');
        if(!isset($_POST['balance']))
            return $this->error('Укажите текущий баланс');

        if($bank_id = $request->route('id')){
            $bank = $this->account->checkingAccounts()->find($bank_id);
            if($bank && $bank instanceof CheckingAccount){
                $bank->correct_history()->create([
                    'old_balance' => $bank->balance,
                    'new_balance' => $balance
                ]);

                $bank->balance = $balance;
                $bank->save();

                return $this->success(compact('bank'));
            }
        }
        return $this->error('Расчётный счёт не найден');
    }
}
