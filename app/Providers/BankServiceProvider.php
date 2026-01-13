<?php

namespace App\Providers;

use App\Http\Services\Bank\Tochka;
use Illuminate\Support\ServiceProvider;
use TochkaApi\HttpAdapters\CurlHttpClient;

class BankServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->bind('tochka', function () {
            return new Tochka(env('TOCHKA_CLIENT_ID'), env('TOCHKA_CLIENT_SECRET'), env('TOCHKA_REDIRECT_URI'), new CurlHttpClient());
        });
    }
}
