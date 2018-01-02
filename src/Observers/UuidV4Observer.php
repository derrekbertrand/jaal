<?php

namespace DialInno\Jaal\Observers;

use Illuminate\Database\Eloquent\Model;
use Ramsey\Uuid\Uuid;

class UuidV4Observer
{
    /**
     * Listen to the User created event.
     *
     * @param  Model  $user
     * @return void
     */
    public function creating(Model $model)
    {
        // if we haven't set the id yet, make one
        if ($model->id === null) {
            $model->id = Uuid::uuid4()->toString();
        }
    }
}
