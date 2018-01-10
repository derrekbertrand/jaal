<?php

namespace DialInno\Jaal\Tests\Data;

/**
 * This provides bad examples.
 */
trait BadExamples
{
    public function badExampleProvider()
    {
        return [
[ 400, 'Bad Payload',
<<<'EXAMPLE_EOF'
{
  "data": [{
    "type": "articles",
    "id": "1",
    "attributes": {
      "title": "JSON API paints my bikeshed!",
      "body": "The shortest article. Ever.",
      "created": "2015-05-22T14:56:29.000Z",
      "updated": "2015-05-22T14:56:28.000Z"
    },
    "relationships": {
      "author: {
        "data": {"id": "42", "type": "people"}
      }
    }
  }]
}
EXAMPLE_EOF
],
[ 400, 'Expected value of type object, found integer instead',
<<<'EXAMPLE_EOF'
42
EXAMPLE_EOF
],
[ 400, 'The context requires one of the following keys: \'data\', \'errors\', \'meta\'',
<<<'EXAMPLE_EOF'
{
  "jsonapi": {
    "version": "1.0"
  }
}
EXAMPLE_EOF
],
[ 400, 'Cannot have keys together in this context: \'data\', \'errors\'',
<<<'EXAMPLE_EOF'
{
  "data": [],
  "errors": [
    {"detail": "I like to write unit tests at 2:30 in the morning."}
  ]
}
EXAMPLE_EOF
],
[ 400, 'Expected value of type string, found double instead',
<<<'EXAMPLE_EOF'
{
  "jsonapi": {
    "version": 1.0
  },
  "meta": {
    "foo": "bar"
  }
}
EXAMPLE_EOF
],
[ 400, '\'_foo\' is not a valid key name',
<<<'EXAMPLE_EOF'
{
  "meta": {
    "_foo": "bar"
  }
}
EXAMPLE_EOF
],
        ];
    }
}
