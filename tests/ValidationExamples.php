<?php

namespace DialInno\Jaal\Tests;

use DialInno\Jaal\Tests\Schemas\AgentSchema;

/**
 * 
 */
trait ValidationExamples
{
    public function validationBadSingleDataProvider()
    {
        return [
[   // verify store rules are enforced
    AgentSchema::class,
    422,
    'store',
    ['first name must be at least 2 characters', 'last name must be a string', 'last name must be at least 2 characters', 'email field is required'],
    ['password'],
<<<'EXAMPLE_EOF'
{
  "data": {
    "type": "agent",
    "attributes": {
      "first_name": "4",
      "last_name": 4,
      "password": "Passw0rd"
    }
  }
}
EXAMPLE_EOF
],
[   // verify update rules are enforced
    AgentSchema::class,
    422,
    'update',
    ['last name must be a string', 'last name must be at least 2 characters'],
    ['required', 'email', 'password', 'first name'],
<<<'EXAMPLE_EOF'
{
  "data": {
    "id": "42",
    "type": "agent",
    "attributes": {
      "last_name": 4
    }
  }
}
EXAMPLE_EOF
],
[   // verify whitelist respects store
    AgentSchema::class,
    400,
    'store',
    ['\'donkey\' is not an allowed key in this context', 'first name', 'last name', 'email', 'password'],
    ['job title'],
<<<'EXAMPLE_EOF'
{
  "data": {
    "type": "agent",
    "attributes": {
      "donkey": "Shrek!"
    }
  }
}
EXAMPLE_EOF
],
[   // verify whitelist respects update
    AgentSchema::class,
    400,
    'update',
    ['\'donkey\' is not an allowed key in this context'],
    ['first name', 'last name', 'job title', 'email', 'password'],
<<<'EXAMPLE_EOF'
{
  "data": {
    "id": "42",
    "type": "agent",
    "attributes": {
      "donkey": "Shrek!"
    }
  }
}
EXAMPLE_EOF
],
        ];
    }

    public function validationGoodSingleDataProvider()
    {
        return [
[   // verify we can return a new agent
    AgentSchema::class,
    'store',
    [
      'first_name' => 'Mark',
      'last_name' => 'Steve',
      'email' => 'demo@demo.com',
      'job_title' => 'Chicken Lord',
    ],
    [
      'password' => 'Passw0rd' //password should not be this, its encrypted
    ],
<<<'EXAMPLE_EOF'
{
  "data": {
    "type": "agent",
    "attributes": {
      "first_name": "Mark",
      "last_name": "Steve",
      "email": "demo@demo.com",
      "job_title": "Chicken Lord",
      "password": "Passw0rd"
    }
  }
}
EXAMPLE_EOF
],
[   // verify we can return a new agent
    AgentSchema::class,
    'update',
    [
      'first_name' => 'Snarf',
    ],
    [
    ],
<<<'EXAMPLE_EOF'
{
  "data": {
    "id": "42",
    "type": "agent",
    "attributes": {
      "first_name": "Snarf"
    },
    "relationships": {
      "tags": {
        "data": [
          {"type": "tag", "id": "a-doe"},
          {"type": "tag", "id": "a-deer"},
          {"type": "tag", "id": "a-female-deer"}
        ]
      }
    }
  }
}
EXAMPLE_EOF
],
        ];
    }
}
