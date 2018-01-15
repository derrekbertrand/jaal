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
     * Return an array of directories to load.
     *
     * Disable this functionality in production by excluding Jaal from package
     * discovery. You can also disable package discovery and override the
     * directories with this method.
     *
     * If you don't want to load an entire directory, you can always use
     * JaalRouter yourself in your routes folder.
     *
     * @return array
     **/
    protected function getLoaders()
    {
        // hacky, but this will point to our testing API
        $this->api_map[\App\Http\Api\V1::class] = 'api';

        // don't process anything; we already did it by hand
        return [];
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
