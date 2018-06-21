<?php

namespace App\Providers;

use App\Services\Common\CryptService;
use Illuminate\Support\ServiceProvider;

class MyCryptServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        \App::bind('MyCrypt',CryptService::class);
    }
}
