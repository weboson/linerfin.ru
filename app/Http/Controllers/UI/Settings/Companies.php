<?php


namespace App\Http\Controllers\UI\Settings;


use App\Models\Account;
use App\Models\OPFType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Companies extends \App\Http\Abstraction\AccountAbstract
{

    public function saveCompany(Request $request){

        if($request->route('id')){
            $account = Account::where([
                'id' => $request->route('id'),
                'user_id' => $this->user->id
            ])->first();
            if(!$account)
                return $this->error('Аккаунт не найден');
        }

        // First validate
        $validated = $request->validate([
            'name' => 'required|min:2|max:255',
            'opf_type' => 'nullable|numeric',
            'subdomain' => 'required|min:3|max:25|regex:/^[a-z_0-9]+$/i',
            'inn' => 'nullable|max:50',
            'kpp' => 'nullable|max:50',
            'ogrn' => 'nullable|max:50',
            'address' => 'nullable|max:150',
            'legal_address' => 'nullable|max:150',
            'director_position' => 'nullable|max:55',
            'director_name' => 'nullable|max:55',
            'accountant_position' => 'nullable|max:55',
            'accountant_name' => 'nullable|max:55'
        ]);

        $type = $validated['opf_type'];
        $subdomain = $validated['subdomain'];


        /* STEP: Check organization type
        -------------------------------------------*/
            $type = OPFType::find($type);
            if(!$type)
                return $this->error('Неверный тип организации');


        /* STEP: Check subdomain
        -------------------------------------------*/
            $subdomainExists = Account::where('subdomain', $subdomain);
            if(!empty($account))
                $subdomainExists = $subdomainExists->where('id', '!=', $account->id);

            $subdomainExists = $subdomainExists->count();

            if($subdomainExists || array_search($subdomain, ['auth', 'my']) !== false)
                return $this->error('Этот домен занят. Пожалуйста, выберите другой');


        /* STEP: Create new company
        -------------------------------------------*/
            if(empty($account)){
                $account = new Account($validated);
                $account->opf()->associate($type);

                Auth::user()->accounts()->save($account);
            }
            else{
                $account->update($validated);
                $account->opf()->associate($type);
                $account->save();
            }

        return $this->success(compact('account'));
    }



    public function removeCompany(Request $request){
        $id = $request->route('id');
        $account = $id ? Account::where(['id' => $id, 'user_id' => $this->user->id])->first() : null;

        if(!$id || empty($account))
            return $this->error('Аккаунт не найден');

        $account->delete();

        return $this->success();
    }
}
