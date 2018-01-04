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
/*
[ AgentSchema::class, 'store',
<<<'EXAMPLE_EOF'
{
  "data": {
    "type": "agent",
    "attributes": {
      "title": "JSON API paints my bikeshed!",
      "body": "The shortest article. Ever.",
      "created": "2015-05-22T14:56:29.000Z",
      "updated": "2015-05-22T14:56:28.000Z"
    }
  }
}
EXAMPLE_EOF
],
*/
        ];
    }
}
