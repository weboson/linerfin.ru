<?php

namespace App\Http\Middleware;

use App\Http\Traits\JsonResponses;
use App\Models\Account;
use App\Models\AccountUser;
use Closure;
use App\Http\Responses\JsonResponse as UiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountMiddleware
{
    use JsonResponses;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        /* STEP: Get data of account
        -------------------------------------------*/
            $subdomain = $request->header('X-Subdomain', $request->route('subdomain'));
            $user_id = Auth::id();

            if($subdomain === 'demo'){
                $account = Account::where([
                    'user_id' => $user_id,
                    'is_demo' => true
                ])->first();
            }
            else{
                $account = Account::whereSubdomain($subdomain)->first();
            }



        /* STEP: Check account
        -------------------------------------------*/
            // check for admin
            if($account && $account->user_id !== $user_id){

                // check for other users
                $exist_user = AccountUser::where([
                    'account_id' => $account->id,
                    'user_id' => $user_id
                ])->count();

                if(!$exist_user)
                    return new JsonResponse(['success' => false, 'msg' => 'Unauthorized'], 403);
            }


        /* STEP: Check response
        -------------------------------------------*/
            $response = $next($request); // get response

            if($response instanceof UiResponse)
                return $response;

            if(!$response) // Response required
                return $this->error();

            if($response instanceof JsonResponse)
                $response = $response->getData(true);

            if(is_array($response)){
                if(!empty($response['errors']) || !empty($response['exception']))
                    return $this->error($response);

                return $this->success($response);
            }

        return $response;
    }


}
