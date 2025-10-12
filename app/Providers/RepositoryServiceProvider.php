<?php

namespace App\Providers;

use App\Interfaces\PricingRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
use App\Repositories\PricingRepository;
use App\Repositories\TransactionRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->bind(PricingRepositoryInterface::class, PricingRepository::class);
        $this->app->bind(TransactionRepositoryInterface::class, TransactionRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
