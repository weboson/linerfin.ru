<?php

namespace App\Http\Controllers;

use App\Exceptions\MessageBagException;
use App\Http\Traits\JsonResponses;
use App\Models\Account;
use App\Models\Attachment;
use App\Models\NDSType;
use App\Models\OPFType;
use App\Models\TaxationSystem;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\ValidationException;

class AccountController
{
    use JsonResponses;

    const DeniedDomains = ['auth', 'my', 'demo', 'linerfin', 'linercrm', 'amocrm', 'dev', 'test',
                           'calc', 'promo', 'live', 'web', 'www', 'account', 'lk', 'client', 'more',
                           'sale', 'sales', 'map', 'tea', 'coffee', 'date', 'time', 'api', 'docs', 'doc', 'help',
                           'support', 'contract', 'email', 'phone', 'name', 'partnership', 'partner', 'common', 'personal',
                           'cost', 'price', 'settings', 'admin', 'administrator', 'testplace'];



    /**
     * Страница выбора компании
     * Page of choose company
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function accountCompaniesView(){
        $companies = [];
        $user = Auth::user();

        if($user && $user instanceof User){
            $companies = $user->accounts->toArray();
            $companies = array_merge($companies, $user->userAccounts->toArray());
        }

        if(empty($companies) || !count($companies))
            return redirect( config('app.account_new_company', 'https://my.linerfin.ru/new-company/') );

        return view('account.my-companies', compact('companies'));
    }



    /**
     * Страница создания компании
     * Page of create company
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function createCompanyView(){
        $types = OPFType::all();
        return view('account.new-company', compact('types'));
    }



    /**
     * Создание компании
     * Handler of creating company
     * @param Request $request
     * @return \App\Http\Responses\JsonResponse
     */
    public function createCompanyHandler(Request $request){

        $account = null;

        try{
            DB::transaction(function() use ($account){

                // First validate
                $rules = [
                    'company_name' => 'required|min:2|max:255',
                    'company_type' => 'nullable|integer',
                    'company_nds_type' => 'nullable|exists:App\Models\NDSType,id',
                    'taxation_system' => 'nullable|exists:App\Models\TaxationSystem,id',
                    'company_subdomain' => 'required|min:3|max:25|regex:/^[a-z_0-9]+$/i',
                    'company_inn' => 'nullable|max:50',
                    'company_kpp' => 'nullable|max:50',
                    'company_address' => 'nullable|max:150',
                    'company_legal_address' => 'nullable|max:150',
                    'checking_account' => 'nullable|max:35',
                    'bank_name' => 'nullable|max:99',
                    'bank_bik' => 'nullable|max:25',
                    'bank_inn' => 'nullable|max:25',
                    'bank_kpp' => 'nullable|max:25',
                    'bank_correspondent' => 'nullable|max:35',
                    'bank_swift' => 'nullable|max:25',

                    // director
                    'director_position' => 'nullable|max:55',
                    'director_name' => 'nullable|max:55',
                    'director_signature_id' => 'nullable|exists:App\Models\Attachment,id',

                    // accountant
                    'accountant_position' => 'nullable|max:55',
                    'accountant_name' => 'nullable|max:55',
                    'accountant_signature_id' => 'nullable|exists:App\Models\Attachment,id',

                    // Company stamp
                    'stamp_id' => 'nullable|exists:App\Models\Attachment,id',

                    // Company logo
                    'logo_attachment_id' => 'nullable|exists:App\Models\Attachment,id',
                ];


                $validator = Validator::make($_REQUEST, $rules);
                if($validator->fails())
                    throw new ValidationException($validator);


                $validated = $validator->validated();

                $type = $validated['company_type'];
                $subdomain = $validated['company_subdomain'];
                $errors = new MessageBag();


                /* STEP: Test errors
                --------------------------------------------*/
                    // test errors
                    // $errors->add('hello', 'Если вы видите это сообщение - тест пройден');


                /* STEP: Check organization type
                -------------------------------------------*/
                    $type = OPFType::find($type);
                    if(!$type)
                        $errors->add('company_type', 'Неверный тип организации.');


                /* STEP: Check subdomain
                -------------------------------------------*/
                    if(!self::checkSubdomain($subdomain))
                        $errors->add('company_subdomain', 'Этот адрес уже занят. Попробуйте другой.');

                        // Response with errors
                        if($errors->any())
                            throw new MessageBagException($errors);



                /* STEP: Check bank
                -------------------------------------------*/
                    $bank = Arr::only($validated, ['checking_account', 'bank_name', 'bank_bik', 'bank_inn', 'bank_kpp', 'bank_correspondent','bank_swift']);


                /* STEP: Prepare data
                -------------------------------------------*/
                    $fields = [
                        'company_inn' =>            'inn',
                        'company_kpp' =>            'kpp',
                        'company_name' =>           'name',
                        'company_address' =>        'address',
                        'company_subdomain' =>      'subdomain',
                        'company_legal_address' =>  'legal_address',
                    ];

                    $data = [];

                    foreach($fields as $input => $field){
                        if($validated[$input])
                            $data[$field] = $validated[$input];
                    }

                    // get positions
                    $data = array_merge($data, Arr::only($validated, ['director_position', 'director_name', 'accountant_position', 'accountant_name']));


                /* STEP: Create new company
                -------------------------------------------*/
                    $account = new Account($data);
                    $account->opf()->associate($type);

                    Auth::user()->accounts()->save($account);


                /* STEP: Associate attachments
                -------------------------------------------*/

                    // nds type
                    if(!empty($validated['company_nds_type']) && $nds_type = NDSType::find($validated['company_nds_type'])){
                        $account->nds_type()->associate($nds_type);
                    }
                    else{
                        $account->nds_type()->disassociate();
                    }

                    // taxation system
                    if(!empty($validated['taxation_system']) && $taxSys = TaxationSystem::find($validated['taxation_system'])){
                        $account->taxation_system()->associate($taxSys);
                    }
                    else{
                        $account->taxation_system()->disassociate();
                    }

                    // logo
                    if(!empty($validated['logo_attachment_id']) && $logo = Attachment::find($validated['logo_attachment_id'])) {
                        $logo->account()->associate($account);
                        $logo->save();
                        $account->logo_attachment()->associate($logo);
                    }

                    // director signature
                    if(!empty($validated['director_signature_id']) && $director_signature = Attachment::find($validated['director_signature_id'])) {
                        $director_signature->account()->associate($account);
                        $director_signature->save();
                        $account->director_signature()->associate($director_signature);
                    }


                    // accountant signature
                    if(!empty($validated['accountant_signature_id']) && $accountant_signature = Attachment::find($validated['accountant_signature_id'])) {
                        $accountant_signature->account()->associate($account);
                        $accountant_signature->save();
                        $account->accountant_signature()->associate($accountant_signature);
                    }



                /* STEP: Company stamp
                --------------------------------------------*/
                    if(!empty($validated['stamp_id']) && $stamp = Attachment::find($validated['stamp_id'])) {
                        $stamp->account()->associate($account);
                        $stamp->save();
                        $account->stamp_attachment()->associate($stamp);
                    }

                    if(!$account->save())
                        throw new \Exception;



                /* STEP: Create account bank
                -------------------------------------------*/
                    if(!empty($bank))
                        $account->checkingAccounts()->create([
                            'name'          => !empty($bank['name']) ? $bank['name'] : 'Расчётный счёт',
                            'num'           => $bank['checking_account'],
                            'bank_name'     => Arr::get($bank, 'bank_name'),
                            'bank_bik'      => Arr::get($bank, 'bank_bik'),
                            'bank_inn'      => Arr::get($bank, 'bank_inn'),
                            'bank_kpp'      => Arr::get($bank, 'bank_kpp'),
                            'bank_swift'    => Arr::get($bank, 'bank_swift'),
                            'bank_correspondent' => Arr::get($bank, 'bank_correspondent'),
                        ]);


                /* STEP: Remove demo account [demo-mod]
                -------------------------------------------*/
                    $user = Auth::user();
                    if($user instanceof User)
                        $user->accounts()->whereIsDemo(true)->delete();

            });
        }
        catch (ValidationException $e){
            $errors = $e->errors();
            return $this->error(compact('errors'));
        }
        catch (MessageBagException $e){
            $errors = $e->getErrors();
            return $this->error(['errors' => $errors->messages()]);
        }
        catch (\Exception $e){
            $message = $e->getMessage() || 'Не удалось сохранить аккаунт';
            return $this->error([
                'message' => print_r($message, true),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTrace()
            ]);
        }

        return $this->success(compact('account'));
    }


    public static function checkSubdomain($subdomain){

        if(mb_strlen($subdomain) < 3 || mb_strlen($subdomain) > 20)
            return false;

        if(!preg_match('/^[a-z_0-9-]+$/i', $subdomain))
            return false;

        if(Account::where('subdomain', $subdomain)->count())
            return false;

        if(false !== array_search($subdomain, self::DeniedDomains))
            return false;

        return true;
    }



}
