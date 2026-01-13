<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


$domain = config('app.domain', 'linerfin.ru');

include 'services.php';




// amoCRM integration
Route::domain("auth.$domain")->prefix('/amocrm')->middleware(['cors.headers'])->group(function(){
    Route::get('/connect', [\App\Http\Controllers\AmoCRM\AmoCRMController::class, 'OAuthRegistration']);
    Route::post('/settings', [\App\Http\Controllers\AmoCRM\AmoCRMController::class, 'saveSettings']);
    Route::post('/allow-access', [\App\Http\Controllers\AmoCRM\GrantAccess::class, 'grantAccessHandler'])->middleware(['auth']);
    Route::get('/get-token', [\App\Http\Controllers\AmoCRM\AmoCRMController::class, 'getPersonalAccessToken']);
    Route::get('/bootstrap', [\App\Http\Controllers\AmoCRM\AmoCRMController::class, 'accountBootstrap'])->middleware(['auth:sanctum', 'cors.headers']);
    Route::get('/bills', [\App\Http\Controllers\AmoCRM\AmoCRMController::class, 'getBills'])->middleware(['auth:sanctum', 'cors.headers']);
    Route::get('/company-bills', [\App\Http\Controllers\AmoCRM\Bills\CompanyBills::class, 'index'])->middleware(['auth:sanctum', 'cors.headers']);

    // settings
    Route::get('/linerfin-settings', [\App\Http\Controllers\AmoCRM\SettingsController::class, 'show'])->middleware(['auth:sanctum', 'cors.headers']);
    Route::post('/linerfin-settings', [\App\Http\Controllers\AmoCRM\SettingsController::class, 'store'])->middleware(['auth:sanctum', 'cors.headers']);

    // webhooks
    Route::prefix('wh')->group(function(){
        Route::post('add-company', \App\Http\Controllers\AmoCRM\Sync\CompanyWebhookSync::class)->name('amocrm.webhooks.company.add');
        Route::post('update-lead', \App\Http\Controllers\AmoCRM\Sync\LeadWebhookStatus::class)->name('amocrm.webhooks.lead.status');
    });

    // sync
    Route::post('counterparties/sync', [\App\Http\Controllers\AmoCRM\Sync\CompaniesSync::class, 'sync'])->middleware(['auth:sanctum', 'cors.headers']);

    Route::post('/logout', '\App\Http\Controllers\AmoCRM\GrantAccess@logout')->middleware(['auth:sanctum', 'cors.headers']);
});


/* UI API Routes
-------------------------------------------*/
Route::domain("{subdomain}.$domain")->middleware(['auth:sanctum', 'account.api', 'cors.headers'])->prefix('ui')->group(function(){

    Route::get('/bootstrap', 'App\Http\Controllers\UI\MainController@bootstrap');

    // Transactions
    Route::get('/transactions', 'App\Http\Controllers\UI\Transactions\TransactionsController@getList');
    Route::post('/transactions/save', 'App\Http\Controllers\UI\Transactions\TransactionsController@create');
    Route::post('/transactions/{id}/save', 'App\Http\Controllers\UI\Transactions\TransactionsController@update');
    Route::get('/transactions/recalculate', 'App\Http\Controllers\UI\Transactions\Calculator@recalculateAll');
    Route::post('/transactions/remove', 'App\Http\Controllers\UI\Transactions\TransactionsController@remove');

    // Graph data
    Route::get('/transaction-graph', 'App\Http\Controllers\UI\Transactions\GraphController@getGraph');
    Route::get('/transaction-graph/pie/{category}', 'App\Http\Controllers\UI\Transactions\GraphController@getGraphPie');


    /* SECTION: Counterparties
    -------------------------------------------*/
    Route::prefix('/counterparties')->group(function(){

        Route::get('/', 'App\Http\Controllers\UI\Counterparty\MainController@getList'); // get list
        Route::post('create', 'App\Http\Controllers\UI\Counterparty\MainController@create'); // create new
        Route::post('delete', 'App\Http\Controllers\UI\Counterparty\MainController@deleteMany'); // Remove many

        // sub-routes with counterparty
        Route::prefix('/{counterparty_id}')->where(['counterparty_id' => '[0-9]+'])->group(function(){

            Route::get('/', 'App\Http\Controllers\UI\Counterparty\MainController@get'); // get single
            Route::post('/update', 'App\Http\Controllers\UI\Counterparty\MainController@update'); // update
            Route::post('/delete', 'App\Http\Controllers\UI\Counterparty\MainController@delete'); // remove

            // Contacts
            Route::get('/contacts', 'App\Http\Controllers\UI\Counterparty\ContactController@contacts'); // contacts
            Route::post('/contacts/create', 'App\Http\Controllers\UI\Counterparty\ContactController@create'); // new contact
            Route::post('/contacts/{contact_id}/update', 'App\Http\Controllers\UI\Counterparty\ContactController@update'); // update contact
            Route::post('/contacts/{contact_id}/delete', 'App\Http\Controllers\UI\Counterparty\ContactController@delete'); // remove contact

            // Accounts
            Route::get('/accounts', 'App\Http\Controllers\UI\Counterparty\AccountController@accounts'); // checking accounts
            Route::post('/accounts/create', 'App\Http\Controllers\UI\Counterparty\AccountController@create'); // new c.account
            Route::post('/accounts/{id}/update', 'App\Http\Controllers\UI\Counterparty\AccountController@update'); // update c.account
            Route::post('/accounts/{id}/delete', 'App\Http\Controllers\UI\Counterparty\AccountController@delete'); // remove c.account

        });


        // Categories
        Route::get('/categories', 'App\Http\Controllers\UI\Counterparty\CategoryController@categories');
        Route::post('categories/create', 'App\Http\Controllers\UI\Counterparty\CategoryController@create');
        Route::post('categories/{id}/update', 'App\Http\Controllers\UI\Counterparty\CategoryController@update');
        Route::post('categories/{id}/delete', 'App\Http\Controllers\UI\Counterparty\CategoryController@delete');

    });


    // Projects
    Route::prefix('/projects')->group(function(){
        Route::get('/', 'App\Http\Controllers\UI\ProjectsController@getProjects');
        Route::post('/create', 'App\Http\Controllers\UI\ProjectsController@create');
        Route::post('/{project_id}/update', 'App\Http\Controllers\UI\ProjectsController@update');
        Route::post('/{project_id}/delete', 'App\Http\Controllers\UI\ProjectsController@delete');
    });


    // Budget items
    Route::prefix('/budget-items')->group(function(){

        // groups
        Route::post('/groups/save', 'App\Http\Controllers\UI\BudgetItemsController@saveGroup');
        Route::post('/groups/{id}/save', 'App\Http\Controllers\UI\BudgetItemsController@saveGroup');
        Route::post('/groups/{id}/delete', 'App\Http\Controllers\UI\BudgetItemsController@removeGroup');


        // main items
        Route::get('/', 'App\Http\Controllers\UI\BudgetItemsController@getBudgetItems');
        Route::post('/create', 'App\Http\Controllers\UI\BudgetItemsController@save');
        Route::post('/save', 'App\Http\Controllers\UI\BudgetItemsController@save');
        Route::post('/archive', 'App\Http\Controllers\UI\BudgetItemsController@toArchive');
        Route::post('/publish', 'App\Http\Controllers\UI\BudgetItemsController@toPublic');
        Route::get('/{category}', 'App\Http\Controllers\UI\BudgetItemsController@getBudgetItems');
        Route::get('/{category}/groups', 'App\Http\Controllers\UI\BudgetItemsController@getGroups');
        Route::post('/{budget_item_id}/update', 'App\Http\Controllers\UI\BudgetItemsController@save');
        Route::post('/{id}/save', 'App\Http\Controllers\UI\BudgetItemsController@save');
//        Route::post('/{budget_item_id}/delete', 'App\Http\Controllers\UI\BudgetItemsController@delete');
    });


    // Bills
    Route::prefix('/bills')->group(function(){

        // Templates [development]
        // Route::get('/templates', 'App\Http\Controllers\UI\Bill\BillTemplateController@templates'); // get templates
        // Route::get('/templates/{id}', 'App\Http\Controllers\UI\Bill\BillTemplateController@templates'); // get template
        // Route::post('{id}/create-template', 'App\Http\Controllers\UI\Bill\BillTemplateController@createTemplate'); // create

        // Main
        Route::get('/', 'App\Http\Controllers\UI\Bill\BillController@bills'); // get bills +
        Route::get('/{id}', 'App\Http\Controllers\UI\Bill\BillController@get'); // get the bill +
        Route::get('/{id}/positions', 'App\Http\Controllers\UI\Bill\BillController@positions'); // get bill's positions +

        // Route::post('/create', 'App\Http\Controllers\UI\Bill\BillController@create'); // create bill +
        // Route::post('{id}/update', 'App\Http\Controllers\UI\Bill\BillController@update'); // update bill +
        // Route::post('{id}/update-positions', 'App\Http\Controllers\UI\Bill\BillController@updatePositions'); // update positions +
        Route::post('{id}/delete', 'App\Http\Controllers\UI\Bill\BillController@delete'); // remove bill +
        Route::post('delete', 'App\Http\Controllers\UI\Bill\BillController@deleteMany'); // remove many
        Route::post('{id}/remove-draft', 'App\Http\Controllers\UI\Bill\BillController@removeDraft'); // remove draft

        // Bill actions
        Route::post('{id}/send', 'App\Http\Controllers\UI\Bill\BillController@send'); // send bill to payment
        // Route::post('{id}/reject', 'App\Http\Controllers\UI\Bill\BillController@reject'); // reject bill | [deprecated 29.04.2022]
        Route::get('{id}/download', 'App\Http\Controllers\UI\Bill\BillController@billView');
        Route::post('{id}/send-notify', 'App\Http\Controllers\UI\Bill\BillController@sendNotifyClient'); // send notify to client


        // Update
        Route::post('save', 'App\Http\Controllers\UI\Bill\BillController@save');
        Route::post('{id}/save', 'App\Http\Controllers\UI\Bill\BillController@save');

        // Statuses
        Route::post('{id}/set-status', 'App\Http\Controllers\UI\Bill\BillStatusController@setStatus');
    });


    // Checking accounts [disabled]
    /*Route::prefix('/checking-accounts')->group(function(){

        Route::get('/', 'App\Http\Controllers\UI\CheckingAccountController@accounts'); // get c.accounts
        Route::get('/{id}', 'App\Http\Controllers\UI\CheckingAccountController@get'); // get the c.a.
        Route::post('create', 'App\Http\Controllers\UI\CheckingAccountController@create'); // create checking account
        Route::post('{id}/update', 'App\Http\Controllers\UI\CheckingAccountController@update'); // update checking account
        Route::post('{id}/delete', 'App\Http\Controllers\UI\CheckingAccountController@delete'); // remove checking account
    });*/


    // Account
    Route::prefix('/account')->group(function(){
        Route::get('/', 'App\Http\Controllers\UI\AccountController@get'); // get account's info
        Route::post('/update', 'App\Http\Controllers\UI\AccountController@update'); // update account's info

        // Account's companies
        Route::prefix('/companies')->group(function(){
            Route::get('/', 'App\Http\Controllers\UI\AccountController@companies'); // get companies
            Route::get('/{id}', 'App\Http\Controllers\UI\AccountController@getCompany'); // get the company
            Route::post('create', 'App\Http\Controllers\UI\AccountController@createCompany'); // create company
            Route::post('{id}/update', 'App\Http\Controllers\UI\AccountController@updateCompany'); // update company
            Route::post('{id}/delete', 'App\Http\Controllers\UI\AccountController@deleteCompany'); // remove company
        });

    });


    // Settings
    Route::prefix('/settings')->group(function(){
        Route::post('change-name', [\App\Http\Controllers\UI\Settings\Common::class, 'saveUserName']);
        Route::post('change-phone', [\App\Http\Controllers\UI\Settings\Common::class, 'savePhone']);
        Route::post('change-email', [\App\Http\Controllers\UI\Settings\Common::class, 'saveEmail']);
        Route::post('change-password', [\App\Http\Controllers\UI\Settings\Common::class, 'changePassword']);
    });

    // Companies
    Route::prefix('/companies')->group(function(){
        Route::post('save', [\App\Http\Controllers\UI\Settings\Companies::class, 'saveCompany']);
        Route::post('{id}/save', [\App\Http\Controllers\UI\Settings\Companies::class, 'saveCompany']);
        Route::post('{id}/remove', [\App\Http\Controllers\UI\Settings\Companies::class, 'removeCompany']);
    });

    // Banks (checking accounts)
    Route::prefix('/banks')->group(function(){
        Route::get('/', [\App\Http\Controllers\UI\Settings\Banks::class, 'getList']);
        Route::post('save', [\App\Http\Controllers\UI\Settings\Banks::class, 'save']);
        Route::post('{id}/save', [\App\Http\Controllers\UI\Settings\Banks::class, 'save']);
        Route::post('{id}/set-balance', [\App\Http\Controllers\UI\Settings\Banks::class, 'setBalance']);
        Route::post('remove', [\App\Http\Controllers\UI\Settings\Banks::class, 'remove']);
    });


    // Reports
    Route::prefix('/reports')->group(function(){
        Route::any('pl', [\App\Http\Controllers\UI\Reports\PLReport::class, 'getData']);
        Route::any('fof', [\App\Http\Controllers\UI\Reports\FoFReport::class, 'getData']);
    });


    // Documents
    Route::prefix('/docs')->group(function(){
        Route::get('', [\App\Http\Controllers\UI\Docs\DocsController::class, 'get']);

        Route::get('/services/sync', [\App\Http\Controllers\UI\Docs\SyncService::class, 'updateService']);
    });

});



// For middlewares single
Route::domain("{subdomain}.$domain")->prefix('ui')->group(function(){

    // Attachments
    Route::prefix('/attachments')->group(function(){
        // private
        Route::middleware(['auth:sanctum', 'account.api'])->group(function(){
            Route::get('', 'App\Http\Controllers\UI\FilesController@getAttachmentList');
            Route::post('', 'App\Http\Controllers\UI\FilesController@saveAttachment');
            Route::post('/{uuid}/remove', 'App\Http\Controllers\UI\FilesController@removeAttachment');
            Route::post('/remove', 'App\Http\Controllers\UI\FilesController@removeMoreAttachment');
        });
        // public
        Route::get('/{uuid}', 'App\Http\Controllers\UI\FilesController@getAttachment');
    });


    // Autocomplete
    Route::prefix('/autocomplete')->middleware(['auth:sanctum'])->group(function(){
        Route::get('counterparty', 'App\Http\Controllers\UI\AutocompleteController@counterparty');
        Route::get('bank', 'App\Http\Controllers\UI\AutocompleteController@bank');
        Route::get('project', 'App\Http\Controllers\UI\AutocompleteController@projects');
        Route::get('budget-item', 'App\Http\Controllers\UI\AutocompleteController@budgetItems');
        Route::get('budget-item-{category}', 'App\Http\Controllers\UI\AutocompleteController@budgetItems');
        Route::get('checking-account', 'App\Http\Controllers\UI\AutocompleteController@checkingAccounts');
    });


    /* SEC: App Directories
    --------------------------------------------*/
        // company types
        Route::get('/company-types', function(Request $request){
            return new \Illuminate\Http\JsonResponse([
                'success' => true,
                'types' => \App\Models\OPFType::all()
            ]);
        });


    // Check subdomain on free
    Route::post('/check-subdomain', function(Request $request){
        $subdomain = $request->input('subdomain');
        $exists = \App\Models\Account::where('subdomain', 'like', $subdomain)->count();
        if($exists || false !== array_search($subdomain, \App\Http\Controllers\AccountController::DeniedDomains))
            return new \App\Http\Responses\JsonResponse(['success' => true, 'free' => false]);
        else
            return new \App\Http\Responses\JsonResponse(['success' => true, 'free' => true]);
    })->middleware(['auth:sanctum']);


    // Generate subdomain by company name
    Route::post('generate-subdomain', function(Request $request){
        $name = $request->input('name');
        if(empty($name))
            return '{"success":true}';

        $name = preg_replace('/^(ООО|ИП|ОАО|ПАО|ЗАО)/i', '', $name);
        $name = preg_replace('/[^0-9а-яa-z]/ui', '', $name);
        $subdomain = transliterator_transliterate("Any-Latin; NFD; [:Nonspacing Mark:] Remove; NFC; [:Punctuation:] Remove; Lower();", $name);
       /* $subdomain = preg_replace('/[ ]/i', '-', trim($name));
        $subdomain = preg_replace('/[-]+/i', '-', $subdomain);*/

        if(!preg_match('/^[a-z_0-9-]+$/i', $subdomain) || strlen($subdomain) < 3 || strlen($subdomain) > 20)
//            return '{"success":true}';
            $subdomain = 'profile';

        $counter = 0;

        do{
            if(!empty($exists))
                $subdomain .= $exists;

            if($counter++ > 100)
                return '{"success":true}';
        }
        while($exists = \App\Models\Account::where('subdomain', 'like', $subdomain)->count() || false !== array_search($subdomain, \App\Http\Controllers\AccountController::DeniedDomains));

        if(!\App\Http\Controllers\AccountController::checkSubdomain($subdomain))
            return '{"success":true}';

        return new \App\Http\Responses\JsonResponse(['success' => true, 'subdomain' => $subdomain]);

    })->middleware(['auth:sanctum']);


});
