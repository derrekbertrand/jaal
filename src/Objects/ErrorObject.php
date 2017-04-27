<?php

namespace DialInno\Jaal\Objects;

use DialInno\Jaal\JsonApi;
use Illuminate\Support\Collection;

/**
 * Responsible for serializing a error object.
 */
class ErrorObject extends MetaObject {

    public function __construct(MetaObject $parent, $data)
    {
        parent::__construct($parent, $data);

        $this->data = (new Collection([
            'title' => 'Error',
            'detail' => 'An error occurred.',
            'status' => '400',
        ]))->merge($this->data);
    }

    public function getHttpStatus()
    {
        if($this->data->has('status'))
            return strval($this->data->get('status'));
        else
            return '400';
    }

    protected function validateMembers()
    {
        $this->data->each(function ($item, $key) {
            //if it is not in the allowed members list, complain
            if(array_search($key, ['id', 'links', 'status', 'code', 'title', 'detail', 'source', 'meta']) === false)
                $this->addError([
                        'title' => 'Invalid Member',
                        'detail' => $key.' is not a valid member name.'
                    ],
                    new Collection([$key])
                );
        });
    }
}
