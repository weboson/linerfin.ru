<?php

namespace App\Http\Controllers\UI;

use App\Http\Abstraction\AccountAbstract;
use App\Http\Controllers\DadataProvider;
use App\Models\Account;
use App\Models\AccountUser;
use App\Models\Counterparty;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;

class AutocompleteController extends AccountAbstract
{

    // protected $constructInitialize = false; // disable authorize


    public function authorize_account()
    {
        $request = $this->request = app()->make('request');

        if(empty($request))
            return true;

        // Subdomain can be from domain or from header
        $subdomain = $request->header('X-Subdomain', $request->route('subdomain'));

        $user_id = Auth::id();
        $this->user = Auth::user();
        $this->dadataProvider = DadataProvider::initClient();

        // set account
        if(!empty($subdomain) && false === array_search($subdomain, ['my', 'auth']) && !empty($user_id)){
            if($subdomain === 'demo')
                $account = Auth::user()->accounts()->where('is_demo', true)->first();
            else
                $account = Account::whereSubdomain($subdomain)->first();

            if(empty($account))
                return false;

            // search by managers
            if($account->user_id !== $user_id){
                $accountUser = AccountUser::where([
                    'account_id' => $account->id,
                    'user_id' => $user_id
                ])->first();

                if(!$accountUser)
                    return false;
            }

            $this->account = $account;
            $this->account_id = $account->id;
        }


        return true;
    }

    /**
     * @var \Dadata\DadataClient
     */
    protected $dadataProvider;



    /**
     * Counterparty autocomplete
     * @param Request $request
     * @return \App\Http\Responses\JsonResponse
     */
    public function counterparty(Request $request){

        $search = $request->input('s', '');
        $autocomplete = [];

        if(!empty($this->account)){
            $counterpartiesBuilder = $this->account->counterparties();

            if(!empty($search)){
                $search .= "*"; // fuzzy search
                $counterpartiesBuilder->whereRaw("MATCH(name,inn,kpp) AGAINST(? IN BOOLEAN MODE)", $search);
            }
            $counterparties = $counterpartiesBuilder->orderByDesc('updated_at')->get();


            foreach($counterparties as $party){
                $autocomplete[] = [
                    'value' => $party->name,
                    'id' => $party->id,
                    'data' => [
                        'inn' => $party->inn,
                        'kpp' => $party->kpp,
                        'ogrn' => $party->ogrn,
                        'address' => ['value' => $party->legal_address],
                        'name' => $party->name
                    ]
                ];
            }
        }

        if(!isset($_GET['from-db']) && !empty($search))
            $autocomplete = array_merge($autocomplete, $this->dadataProvider->suggest('party', $search, 5));

        // remove duplicates
        if($autocomplete){
            $unique = [];
            foreach($autocomplete as $key => $item){
                $inn = Arr::get($item, 'data.inn');
                if(!$inn) continue;

                if(!empty($unique[$inn]))
                    unset($autocomplete[$key]);
                else
                    $unique[$inn] = true;
            }

        }

        return $this->success(compact('autocomplete'));
    }


    /**
     * Bank autocomplete
     * @param Request $request
     * @return \App\Http\Responses\JsonResponse
     */
    public function bank(Request $request){
        $search = $request->input('s');

        if(!$search)
            return $this->success();

        $autocomplete = $this->dadataProvider->suggest('bank', $search, 5);

        return $this->success(compact('autocomplete'));
    }




    public function projects(Request $request){
        $search = $request->input('s');
        if(empty($this->account)) return $this->success();

        $projectsBuilder = $this->account->projects();

        if(empty($search))
            return $this->success(['autocomplete' => $projectsBuilder->limit(7)->get()]);

        // fuzzy search
        $search .= "*";

        $projects = $projectsBuilder->where('archived', 0)
            ->whereRaw("MATCH(name,comment) AGAINST(? IN BOOLEAN MODE)", $search)
            ->limit(7)
            ->get();

        return $this->success([
            'autocomplete' => $projects
        ]);
    }


    public function budgetItems(Request $request){
        $search = $request->input('s');
        if(empty($this->account)) return $this->success();

        $builder = $this->account->budgetItems();

        // Choose category
        $category = $request->route('category');
        if($category && false !== array_search($category, ['income', 'expense', 'transfer']))
            $builder->where('category', $category);


        // Search
        if(empty($search))
            return $this->success(['autocomplete' => $builder->limit(7)->get()]);


        // fuzzy search
        $search .= "*";

        $builder->where('archived', 0)
            ->whereRaw("MATCH(name,comment) AGAINST(? IN BOOLEAN MODE)", $search)
            ->limit(7);


        return $this->success([
            'autocomplete' => $builder->get()
        ]);
    }


    public function checkingAccounts(Request $request){
        $search = $request->input('s');
        if(empty($this->account)) return $this->success();

        $builder = $this->account->checkingAccounts();

        $exclude = $request->input('exclude');
        if($exclude){
            $exclude = explode(',', $exclude);
            $builder->whereNotIn('id', $exclude);
        }

        if(empty($search))
            return $this->success(['autocomplete' => $builder->limit(7)->get()]);

        $items = $builder->where('name', 'like', "%$search%")
            ->orWhere('num', 'like', "%$search%")
            ->orWhere('bank_name', 'like', "%$search%")
            ->orWhere('comment', 'like', "%$search%")
            ->limit(7)
            ->get();

        return $this->success([
            'autocomplete' => $items
        ]);
    }



}
