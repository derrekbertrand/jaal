<?php

namespace DialInno\Jaal\Tests\Schemas;

use DialInno\Jaal\Schema;
use DialInno\Jaal\Tests\Models\Agent;

class AgentSchema extends Schema
{
    protected function createHydrated()
    {
        // a naive approach to filling out the model
        $model = (new Agent)->forceFill($this->resource->attributes());

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

    protected function scalarStoreWhitelist()
    {
        return [
            'first_name',
            'last_name',
            'job_title',
            'email',
            'password',
        ];
    }

    protected function scalarUpdateWhitelist()
    {
        return $this->scalarStoreWhitelist();
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
