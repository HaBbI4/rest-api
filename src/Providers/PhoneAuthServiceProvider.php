<?php

namespace Webkul\RestApi\Providers;

use App\Services\PhoneVerificationService;
use App\Services\PhoneVerificationServiceInterface;
use Illuminate\Support\ServiceProvider;

class PhoneAuthServiceProvider extends ServiceProvider
{

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(PhoneVerificationServiceInterface::class, function ($app) {
            return $app->make(PhoneVerificationService::class);
        });
    }

}
