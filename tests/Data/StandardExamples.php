<?php

namespace DialInno\Jaal\Tests\Data;

/**
 * This provides the JSON from the website.
 *
 * Pretty much every example of interest is provided here. There's both requests
 * and responses, good ones and error blocks. They should all be able to be
 * parsed and deserialized by the library.
 */
trait StandardExamples
{
    public function standardExampleProvider()
    {
        return [
[ 200, // Examples from this page http://jsonapi.org/examples/
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
      "author": {
        "data": {"id": "42", "type": "people"}
      }
    }
  }],
  "included": [
    {
      "type": "people",
      "id": "42",
      "attributes": {
        "name": "John",
        "age": 80,
        "gender": "male"
      }
    }
  ]
}
EXAMPLE_EOF
],
[ 200,
<<<'EXAMPLE_EOF'
{
  "data": [{
    "type": "articles",
    "id": "1",
    "attributes": {
      "title": "JSON API paints my bikeshed!",
      "body": "The shortest article. Ever."
    },
    "relationships": {
      "author": {
        "data": {"id": "42", "type": "people"}
      }
    }
  }],
  "included": [
    {
      "type": "people",
      "id": "42",
      "attributes": {
        "name": "John"
      }
    }
  ]
}
EXAMPLE_EOF
],
[ 200,
<<<'EXAMPLE_EOF'
{
  "data": [{
    "type": "articles",
    "id": "1",
    "attributes": {
      "title": "JSON API paints my bikeshed!",
      "body": "The shortest article. Ever."
    }
  }],
  "included": [
    {
      "type": "people",
      "id": "42",
      "attributes": {
        "name": "John"
      }
    }
  ]
}
EXAMPLE_EOF
],
[ 200,
<<<'EXAMPLE_EOF'
{
  "meta": {
    "total-pages": 13
  },
  "data": [
    {
      "type": "articles",
      "id": "3",
      "attributes": {
        "title": "JSON API paints my bikeshed!",
        "body": "The shortest article. Ever.",
        "created": "2015-05-22T14:56:29.000Z",
        "updated": "2015-05-22T14:56:28.000Z"
      }
    }
  ],
  "links": {
    "self": "http://example.com/articles?page[number]=3&page[size]=1",
    "first": "http://example.com/articles?page[number]=1&page[size]=1",
    "prev": "http://example.com/articles?page[number]=2&page[size]=1",
    "next": "http://example.com/articles?page[number]=4&page[size]=1",
    "last": "http://example.com/articles?page[number]=13&page[size]=1"
  }
}
EXAMPLE_EOF
],
[ 422,
<<<'EXAMPLE_EOF'
{
  "errors": [
    {
      "status": "422",
      "source": { "pointer": "/data/attributes/first-name" },
      "title":  "Invalid Attribute",
      "detail": "First name must contain at least three characters."
    }
  ]
}
EXAMPLE_EOF
],
[ 500,
<<<'EXAMPLE_EOF'
{
  "errors": [
    {
      "status": "403",
      "source": { "pointer": "/data/attributes/secret-powers" },
      "detail": "Editing secret powers is not authorized on Sundays."
    },
    {
      "status": "422",
      "source": { "pointer": "/data/attributes/volume" },
      "detail": "Volume does not, in fact, go to 11."
    },
    {
      "status": "500",
      "source": { "pointer": "/data/attributes/reputation" },
      "title": "The backend responded with an error",
      "detail": "Reputation service not responding after three requests."
    }
  ]
}
EXAMPLE_EOF
],
[ 400,
<<<'EXAMPLE_EOF'
{
  "errors": [
    {
      "source": { "pointer": "/data/attributes/first-name" },
      "title": "Invalid Attribute",
      "detail": "First name must contain at least three characters."
    },
    {
      "source": { "pointer": "/data/attributes/first-name" },
      "title": "Invalid Attribute",
      "detail": "First name must contain an emoji."
    }
  ]
}
EXAMPLE_EOF
],
[ 400,
<<<'EXAMPLE_EOF'
{
  "jsonapi": { "version": "1.0" },
  "errors": [
    {
      "code":   "123",
      "source": { "pointer": "/data/attributes/first-name" },
      "title":  "Value is too short",
      "detail": "First name must contain at least three characters."
    },
    {
      "code":   "225",
      "source": { "pointer": "/data/attributes/password" },
      "title": "Passwords must contain a letter, number, and punctuation character.",
      "detail": "The password provided is missing a punctuation character."
    },
    {
      "code":   "226",
      "source": { "pointer": "/data/attributes/password" },
      "title": "Password and password confirmation do not match."
    }
  ]
}
EXAMPLE_EOF
],
[ 400,
<<<'EXAMPLE_EOF'
{
  "errors": [
    {
      "source": { "pointer": "" },
      "detail":  "Missing `data` Member at document's top level."
    }
  ]
}
EXAMPLE_EOF
],
[ 400,
<<<'EXAMPLE_EOF'
{
  "errors": [
    {
      "source": { "parameter": "include" },
      "title":  "Invalid Query Parameter",
      "detail": "The resource does not have an `author` relationship path."
    }
  ]
}
EXAMPLE_EOF
],
[ 200, // Complete example from http://jsonapi.org/format/#document-compound-documents
<<<'EXAMPLE_EOF'
{
  "data": [{
    "type": "articles",
    "id": "1",
    "attributes": {
      "title": "JSON API paints my bikeshed!"
    },
    "links": {
      "self": "http://example.com/articles/1"
    },
    "relationships": {
      "author": {
        "links": {
          "self": "http://example.com/articles/1/relationships/author",
          "related": "http://example.com/articles/1/author"
        },
        "data": { "type": "people", "id": "9" }
      },
      "comments": {
        "links": {
          "self": "http://example.com/articles/1/relationships/comments",
          "related": "http://example.com/articles/1/comments"
        },
        "data": [
          { "type": "comments", "id": "5" },
          { "type": "comments", "id": "12" }
        ]
      }
    }
  }],
  "included": [{
    "type": "people",
    "id": "9",
    "attributes": {
      "first-name": "Dan",
      "last-name": "Gebhardt",
      "twitter": "dgeb"
    },
    "links": {
      "self": "http://example.com/people/9"
    }
  }, {
    "type": "comments",
    "id": "5",
    "attributes": {
      "body": "First!"
    },
    "relationships": {
      "author": {
        "data": { "type": "people", "id": "2" }
      }
    },
    "links": {
      "self": "http://example.com/comments/5"
    }
  }, {
    "type": "comments",
    "id": "12",
    "attributes": {
      "body": "I like XML better"
    },
    "relationships": {
      "author": {
        "data": { "type": "people", "id": "9" }
      }
    },
    "links": {
      "self": "http://example.com/comments/12"
    }
  }]
}
EXAMPLE_EOF
],
[ 200, // From fetching data http://jsonapi.org/format/#fetching-resources
<<<'EXAMPLE_EOF'
{
  "links": {
    "self": "http://example.com/articles"
  },
  "data": [{
    "type": "articles",
    "id": "1",
    "attributes": {
      "title": "JSON API paints my bikeshed!"
    }
  }, {
    "type": "articles",
    "id": "2",
    "attributes": {
      "title": "Rails is Omakase"
    }
  }]
}
EXAMPLE_EOF
],
[ 200,
<<<'EXAMPLE_EOF'
{
  "links": {
    "self": "http://example.com/articles"
  },
  "data": []
}
EXAMPLE_EOF
],
[ 200,
<<<'EXAMPLE_EOF'
{
  "links": {
    "self": "http://example.com/articles/1"
  },
  "data": {
    "type": "articles",
    "id": "1",
    "attributes": {
      "title": "JSON API paints my bikeshed!"
    },
    "relationships": {
      "author": {
        "links": {
          "related": "http://example.com/articles/1/author"
        }
      }
    }
  }
}
EXAMPLE_EOF
],
[ 200,
<<<'EXAMPLE_EOF'
{
  "links": {
    "self": "http://example.com/articles/1/author"
  },
  "data": null
}
EXAMPLE_EOF
],
[ 200, // From CRUD http://jsonapi.org/format/#crud
<<<'EXAMPLE_EOF'
{
  "data": {
    "type": "photos",
    "attributes": {
      "title": "Ember Hamster",
      "src": "http://example.com/images/productivity.png"
    },
    "relationships": {
      "photographer": {
        "data": { "type": "people", "id": "9" }
      }
    }
  }
}
EXAMPLE_EOF
],
[ 200,
<<<'EXAMPLE_EOF'
{
  "data": {
    "type": "photos",
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "attributes": {
      "title": "Ember Hamster",
      "src": "http://example.com/images/productivity.png"
    }
  }
}
EXAMPLE_EOF
],
[ 200,
<<<'EXAMPLE_EOF'
{
  "data": {
    "type": "photos",
    "id": "550e8400-e29b-41d4-a716-446655440000",
    "attributes": {
      "title": "Ember Hamster",
      "src": "http://example.com/images/productivity.png"
    },
    "links": {
      "self": "http://example.com/photos/550e8400-e29b-41d4-a716-446655440000"
    }
  }
}
EXAMPLE_EOF
],
[ 200,
<<<'EXAMPLE_EOF'
{
  "data": {
    "type": "articles",
    "id": "1",
    "attributes": {
      "title": "To TDD or Not"
    }
  }
}
EXAMPLE_EOF
],
[ 200,
<<<'EXAMPLE_EOF'
{
  "data": {
    "type": "articles",
    "id": "1",
    "attributes": {
      "title": "To TDD or Not",
      "text": "TLDR; It's complicated... but check your test coverage regardless."
    }
  }
}
EXAMPLE_EOF
],
[ 200,
<<<'EXAMPLE_EOF'
{
  "data": {
    "type": "articles",
    "id": "1",
    "relationships": {
      "author": {
        "data": { "type": "people", "id": "1" }
      }
    }
  }
}
EXAMPLE_EOF
],
[ 200,
<<<'EXAMPLE_EOF'
{
  "data": {
    "type": "articles",
    "id": "1",
    "relationships": {
      "tags": {
        "data": [
          { "type": "tags", "id": "2" },
          { "type": "tags", "id": "3" }
        ]
      }
    }
  }
}
EXAMPLE_EOF
],
[ 200,
<<<'EXAMPLE_EOF'
{
  "data": { "type": "people", "id": "12" }
}
EXAMPLE_EOF
],
[ 200,
<<<'EXAMPLE_EOF'
{
  "data": null
}
EXAMPLE_EOF
],
[ 200,
<<<'EXAMPLE_EOF'
{
  "data": [
    { "type": "tags", "id": "2" },
    { "type": "tags", "id": "3" }
  ]
}
EXAMPLE_EOF
],
[ 200,
<<<'EXAMPLE_EOF'
{
  "data": []
}
EXAMPLE_EOF
],
        ];
    }
}
