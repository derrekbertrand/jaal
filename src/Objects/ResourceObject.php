<?php

namespace DialInno\Jaal\Objects;

use DialInno\Jaal\JsonApi;
use Illuminate\Support\Collection;

/**
 * Responsible for serializing a resource object.
 */
class ResourceObject extends MetaObject {

    protected static $obj_name = 'resource';

    protected function validateMembers()
    {
        //todo: not required if generated client side...
        // if(!$this->data->has('id'))
        //     $this->addError([
        //             'title' => 'ID Required',
        //             'detail' => 'Resource objects require an id member.'
        //         ],
        //         new Collection()
        //     );

        if(!$this->data->has('type'))
            $this->addError([
                    'title' => 'Type Required',
                    'detail' => 'Resource objects require a type member.'
                ],
                new Collection()
            );

        $this->data->each(function ($item, $key) {
            //if it is not in the allowed members list, complain
            if(array_search($key, ['id', 'type', 'attributes', 'relationships', 'links', 'meta']) === false)
                $this->addError([
                        'title' => 'Invalid Member',
                        'detail' => $key.' is not a valid member name.'
                    ],
                    new Collection([$key])
                );
        });
    }
}
