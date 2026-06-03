<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Domain\User\Repositories\UserRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentUserRepository;
use App\Domain\Category\Repositories\CategoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentCategoryRepository;
use App\Domain\Product\Repositories\ProductRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentProductRepository;
use App\Domain\Inventory\Repositories\InventoryRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentInventoryRepository;
use App\Domain\CashierSession\Repositories\CashierSessionRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentCashierSessionRepository;
use App\Domain\Rack\Repositories\RackRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentRackRepository;
use App\Domain\Pos\Repositories\PosRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentPosRepository;
use App\Domain\Sales\Repositories\SalesRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentSalesRepository;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
        $this->app->bind(
            UserRepositoryInterface::class,
            EloquentUserRepository::class,
        );
        $this->app->bind(
            CategoryRepositoryInterface::class,
            EloquentCategoryRepository::class
        );
        $this->app->bind(
            ProductRepositoryInterface::class,
            EloquentProductRepository::class
        );
        $this->app->bind(
            InventoryRepositoryInterface::class,
            EloquentInventoryRepository::class

        );
        $this->app->bind(
            CashierSessionRepositoryInterface::class,
            EloquentCashierSessionRepository::class
        );
        $this->app->bind(
            RackRepositoryInterface::class,
            EloquentRackRepository::class
        );
        $this->app->bind(
            PosRepositoryInterface::class,
            EloquentPosRepository::class
        );
        $this->app->bind(
            SalesRepositoryInterface::class,
            EloquentSalesRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
