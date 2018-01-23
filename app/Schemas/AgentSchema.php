<?php

namespace App\Schemas;

use DialInno\Jaal\Schema;
use App\Models\Agent;

class AgentSchema extends Schema
{
    public static $resource_type = 'agent';
    public static $model = Agent::class;
    public static $sort_whitelist = ['first_name', 'last_name', 'job_title'];
    public static $min_limit = 5;

    protected function attributeStoreRules()
    {
        return [
            'first_name' => 'required|string|min:2|max:31',
            'last_name' => 'required|string|min:2|max:31',
            'job_title' => 'string|min:2|max:31',
            'email' => 'required|email|max:127',
            'password' => ['required','string','min:8','max:120'],
        ];
    }

    public static function relationshipSchemas(): array
    {
        return [
            'accounts' => AccountSchema::class,
            'tags' => TagSchema::class,
        ];
    }

    protected function attributeUpdateRules()
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
