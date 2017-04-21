<?php

namespace DialInno\Jaal\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use DialInno\Jaal\JsonApiSerializable;

class Skill extends Model
{
    use JsonApiSerializable;

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}
