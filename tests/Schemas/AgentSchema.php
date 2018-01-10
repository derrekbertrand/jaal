<?php

namespace DialInno\Jaal\Tests\Schemas;

use DialInno\Jaal\Schema;
use DialInno\Jaal\Tests\Models\Agent;

class AgentSchema extends Schema
{
    protected function createHydrated()
    {
        // a naive approach to filling out the model
        $model = (new Agent)->forceFill($this->resource->attributes()->toArray());

        return $model;
    }

    protected function scalarStoreRules()
    {
        return [
            'first_name' => 'required|string|min:2|max:31',
            'last_name' => 'required|string|min:2|max:31',
            'job_title' => 'string|min:2|max:31',
            'email' => 'required|email|max:127',
            'password' => ['required','string','min:8','max:120'],
        ];
    }

    protected function updateRelationMap()
    {
        return [
            'accounts' => ['account' => AccountSchema::class],
            'tags' => ['tag' => TagSchema::class]
        ];
    }

    protected function scalarUpdateRules()
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
