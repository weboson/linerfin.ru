<?php


namespace App\Http\Abstraction;


use App\Exceptions\AccountAuthorizeException;
use App\Http\Controllers\AmoCRM\AmoCRMController;
use App\Http\Controllers\Controller;
use App\Http\Traits\JsonResponses;
use App\Models\Account;
use App\Models\AccountUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

abstract class AccountAbstract extends Controller
{
    use JsonResponses;

    /** @var Account */
    protected $account;
    protected $account_id;
    protected $subdomain;
    protected $user;

    /** @var Request */
    protected $request;

    protected $middlewareAuthorize = true;
    protected $constructAuthorize = false;

    public function __construct(Account $account = null){

        if($account){
            $this->account = $account;
            $this->user = $account->user;
            $this->account_id = $account->id;
        }

        if($this->constructAuthorize) {
            $this->authorize_account_handler();
        }

        if($this->middlewareAuthorize){
            $this->middleware(function($request, $next){

                if(!$this->authorize_account_handler())
                    return false;

                return $next($request);
            });
        }
    }


    public function authorize_account_handler(){
        try{
            $result = $this->authorize_account();
        }
        catch(AccountAuthorizeException $e){
            $error_response = $this->error($e->getResponseData(), $e->getMessage(), $e->getResponseCode());
            echo $error_response->content();
            die; // no comments o_o"
        }

        return $result;
    }


    /**
     * Авторизовать аккаунт
     * Authorize account
     * subdomain => account ?
     * @return bool
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws AccountAuthorizeException
     */
    public function authorize_account(){

        if($this->user && $this->account)
            return true;

        $request = $this->request = app()->make('request');

        if(empty($request))
            return false;

        // Subdomain can be from domain or from header
        $subdomain = $request->header('X-Subdomain', $request->route('subdomain'));

        $user_id = Auth::id();

        if($subdomain && $user_id){

            $account = $this->getAccount($subdomain);
            if(empty($account))
                throw new AccountAuthorizeException('Аккаунт не найден');

            // check for admin
            if($account->user_id === $user_id){
                $this->setAccount($account);
                return true;
            }

            // check for other users
            $exist_user = AccountUser::where([
                'account_id' => $account->id,
                'user_id' => $user_id
            ])->count();

            if($exist_user){
                $this->setAccount($account);
                return true;
            }

        }

        throw new AccountAuthorizeException('Доступ запрещен');
    }

    /**
     * Get account by subdomain
     * @param $subdomain
     * @return false
     */
    protected function getAccount($subdomain){

        $user = Auth::user();
        if(empty($user))
            return false;

        if($subdomain === 'demo')
            return $user->accounts()->where('is_demo', true)->first();

        return Account::whereSubdomain($subdomain)->first();
    }


    protected function setAccount($account){
        if($account){
            $this->account = $account;
            $this->account_id = $account->id;
            $this->subdomain = $account->subdomain;
            $this->user = Auth::user();
        }
    }


    // Возвращает инстанс класса
    // При объявлении контроллера из другого
    // рекомендуется использовать данный метод
    public static function withAuthorize(){
        $instance = new static();
        $instance->authorize_account();

        return $instance;
    }





    // --------------- вспомогательные методы ---------------



    // разрешенные зависимости
    protected $allowedWith;


    /**
     * Возвращает массив зависимостей указанных в $_GET['with']
     * @param array|null $with
     * @return array
     */
    protected function requestWith($with = null){
        if(empty($with))
            $with = $this->request->get('with');

        if(!empty($with) && (is_array($with) || is_string($with))){

            if(!is_array($with) && is_string($with))
                $with = explode(',', $with);

            // Проверка на разрешенные зависимости
            if(!empty($this->allowedWith) && is_array($this->allowedWith)){
                $withTemp = [];
                foreach($this->allowedWith as $allowed){
                    if(false !== array_search($allowed, $with))
                        $withTemp[] = $allowed;
                }
                $with = $withTemp;
            }

            return $with;
        }
        return [];
    }


    /**
     * Привязывает зависимости из $_GET['with'] к запросу
     * @param Builder $builder
     * @param array $withToo
     * @return array
     */
    protected function bindWith(Builder $builder, array $withToo = []){
        $with = $this->requestWith();
        if(!empty($withToo))
            $with = array_merge($with, $withToo);

        $builder->with($with);

        return $with;
    }


    /**
     * Если указан amocrm-subdomain - привязываем
     * @param Builder $builder
     */
    protected function bindAmoCRM(Builder $builder){
        $account = AmoCRMController::getAccount();
        if($account)
            $builder->where('amocrm_account_id', $account->id);

        // lead_id
        $lead_id = $this->request->get('lead_id');
        if($lead_id)
            $builder->where('amocrm_lead_id', $lead_id);

        return $account;
    }


    /**
     * Привязывает пагинацию
     * учитывая $_GET['ppc'] (per page count)
     * @param Builder $builder
     * @param int $page_per_count
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    protected function paginate(Builder &$builder, int $page_per_count = 10){
        $ppc = $this->request->get('ppc', $page_per_count);
        return $builder->paginate($ppc);
    }



    protected $validateRules = []; // правила валидации

    /** Вернет данные используя валидацию
     * @param array $rules
     * @return array
     */
    protected function validatedRequest(array $rules = []){
        if(empty($this->validateRules) && empty($rules)) return [];
        $rules = empty($rules) ? $this->validateRules : array_merge($this->validateRules, $rules);
        return $this->request->validate($rules);
    }


    /**
     * Возвращает конструктор с учетом account_id
     * @param string $model
     * @param array $where
     * @return false|Builder
     */
    protected function getBuilder(string $model, array $where = []){
        $conditions['account_id'] = $this->account_id;
        if(!empty($where)) $conditions = array_merge($conditions, $where);
        return call_user_func_array([$model, 'where'], [$conditions]);
    }

}
