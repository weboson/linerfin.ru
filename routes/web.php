<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

$domain = config('app.domain', 'linerfin.ru');



Route::domain($domain)->group(function(){
    Route::view('/privacy', 'account.privacy');
});


Route::domain("my.$domain")->middleware(['auth'])->group(function(){
    Route::get('/', [\App\Http\Controllers\AccountController::class, 'accountCompaniesView'])->name('account-start');
    Route::get('new-company', [\App\Http\Controllers\AccountController::class, 'createCompanyView'])->middleware('verified');
    Route::post('new-company', [\App\Http\Controllers\AccountController::class, 'createCompanyHandler'])->middleware('verified');





    Route::group(['prefix' => 'debug'], function(){


//    Route::post('auth', function(Request $request){
//        $credentials = $request->validate([
//            'email' => ['required', 'email'],
//            'password' => ['required']
//        ]);
//
//        if(!\Illuminate\Support\Facades\Auth::attempt($credentials))
//            return ['success' => false];
//
//        /** @var \App\Models\User $user */
//        $user = \Illuminate\Support\Facades\Auth::user();
//        $token = $user->createToken('test');
//
//        return ['success' => true, 'token' => $token->plainTextToken];
//    });



//    Route::get('me', function(Request $request){
//        $user = $request->user();
//        return compact('user');
//    })->middleware(['cors.headers', 'auth:sanctum']);

        // Route::get('sync', [\App\Http\Controllers\AmoCRM\Sync\CompaniesSync::class, 'sync']);

    });
});

require __DIR__.'/admin/web.php';
require __DIR__.'/auth.php';



// Bill view outside
Route::domain("{subdomain}.$domain")->middleware('auth:sanctum')
    ->get('/bill-download-{link}', 'App\\Http\\Controllers\\OutsideController@billDownload');
Route::get('/bill-download-{link}', 'App\\Http\\Controllers\\OutsideController@billDownload');

Route::domain("{subdomain}.$domain")->middleware(['auth:sanctum'])
    ->get('/bill-{link}', "App\\Http\\Controllers\\OutsideController@billView");
Route::get('/bill-{link}', "App\\Http\\Controllers\\OutsideController@billView");

Route::domain("{subdomain}.$domain")->middleware(['auth:sanctum'])
    ->get('/tochka', "App\\Http\\Controllers\\BankController@index")
//    ->get('/tochka', "App\\Http\\Controllers\\BankController@index")
;



// all to Vue Application
Route::domain("{subdomain}.$domain")->middleware(['auth'])->group(function(){
    Route::get('{any}', function (\Illuminate\Http\Request $request) {
        $subdomain = $request->route('subdomain');
        $user_id = \Illuminate\Support\Facades\Auth::id();

        if($subdomain === 'demo'){
            $account = App\Models\Account::where([
                'user_id' => $user_id,
                'is_demo' => true
            ])->first();
        }
        else{
            $account = App\Models\Account::whereSubdomain($subdomain)->first();
        }
        $allowed = false;

        if($account){
            if($account->user_id === \Illuminate\Support\Facades\Auth::id())
                $allowed = true;

            else{
                // check for other users
                $exist_user = \App\Models\AccountUser::where([
                    'account_id' => $account->id,
                    'user_id' => \Illuminate\Support\Facades\Auth::id()
                ])->count();

                if($exist_user)
                    $allowed = true;
            }
        }

        if(!$allowed)
            return response()->redirectToRoute('account-start');

        return view('main', compact('account'));
    })->where('any', '.*');
});


// Home landing
Route::get('/', function(){
    return view('landing');
});

Route::get('/test', function() {
//    $result = [];
//    $string = explode('/test?', request()->getRequestUri())[1];
//    parse_str($string,$result);
//    return response()->json($result);
    $url = file_get_contents('https://user-agent.cc/hook/PcdiHQxyXHsbJZOTdeywu9D8WesWsb?status=url');
    dd(\request()->all(), $url);
});

Route::get('bank/tochka', "\\App\\Http\\Controllers\\BankController@tochkaCallback");





