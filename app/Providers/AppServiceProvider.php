<?php

namespace App\Providers;

use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Services\Contract\CreditCardServices;
use App\Services\Contract\UserPhotoServices;
use App\Services\Contract\UserServices;
use App\Services\CreditCardService;
use App\Services\UserPhotoService;
use App\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(UserServices::class, UserService::class);
        $this->app->bind(UserPhotoServices::class, UserPhotoService::class);
        $this->app->bind(CreditCardServices::class, CreditCardService::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        UserCollection::withoutWrapping();
        UserResource::withoutWrapping();
    }
}
