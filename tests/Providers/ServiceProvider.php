<?php

namespace DialInno\Jaal\Tests\Providers;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
use DialInno\Jaal\Tests\Models\Account;
use DialInno\Jaal\Tests\Models\Agent;
use DialInno\Jaal\Tests\Models\Contact;
use DialInno\Jaal\Observers\UuidV4Observer;


class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Account::observe(UuidV4Observer::class);
        Agent::observe(UuidV4Observer::class);
        Contact::observe(UuidV4Observer::class);
    }
}
