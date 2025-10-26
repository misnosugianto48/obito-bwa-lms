<?php

namespace App\Providers;

use App\Interfaces\CourseRepositoryInterface;
use App\Interfaces\PricingRepositoryInterface;
use App\Interfaces\TransactionRepositoryInterface;
use App\Repositories\CourseRepository;
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
        $this->app->bind(CourseRepositoryInterface::class, CourseRepository::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
