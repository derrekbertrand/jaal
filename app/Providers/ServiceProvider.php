<?php

namespace App\Providers;

use App\Models\Account;
use App\Models\Agent;
use App\Models\Contact;
use DialInno\Jaal\JaalProvider;
use DialInno\Jaal\Observers\UuidV4Observer;


class ServiceProvider extends JaalProvider
{
    /**
     * Get the application path with any segments glued on.
     *
     * @param string|array $segments
     * @return string
     */
    protected function glueBasePath($segments)
    {
        // because of Orchestra we set it differently
        $this->base_path = realpath(__DIR__.'/..');

        return parent::glueBasePath($segments);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        Account::observe(UuidV4Observer::class);
        Agent::observe(UuidV4Observer::class);
        Contact::observe(UuidV4Observer::class);
    }
}
