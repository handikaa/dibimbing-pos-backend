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
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
