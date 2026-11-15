<?php

namespace App\Providers;

use App\Contracts\PaymentGateway;
use App\Services\Payments\FakeGateway;
use App\Services\Payments\StripeGateway;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(PaymentGateway::class, function () {
            return config('services.payments.driver') === 'fake'
                ? new FakeGateway()
                : new StripeGateway();
        });
    }

    public function boot(): void
    {
        //
    }
}
