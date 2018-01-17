<?php

namespace App\Schemas;

use DialInno\Jaal\Schema;
use App\Models\Agent;

class AgentSchema extends Schema
{
    public static $resource_type = 'agent';

    protected function createHydrated()
    {
        // a naive approach to filling out the model
        $model = (new Agent)->forceFill($this->resource->get('attributes', collect())->toArray());

        return $model;
    }

    protected function attributesStoreRules()
    {
        return [
            'first_name' => 'required|string|min:2|max:31',
            'last_name' => 'required|string|min:2|max:31',
            'job_title' => 'string|min:2|max:31',
            'email' => 'required|email|max:127',
            'password' => ['required','string','min:8','max:120'],
        ];
    }

    protected function toManyUpdateMap()
    {
        return [
            'accounts' => 'account',
            'tags' => 'tag',
        ];
    }

    protected function attributesUpdateRules()
    {
        return [
            'first_name' => 'string|min:2|max:31',
            'last_name' => 'string|min:2|max:31',
            'job_title' => 'min:2|max:31',
            'email' => 'email|max:127',
            'password' => ['string','min:8','max:120'],
        ];
    }
}
