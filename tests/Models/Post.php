<?php

namespace DialInno\Jaal\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use DialInno\Jaal\JsonApiSerializable;

class Post extends Model
{
    use JsonApiSerializable;

    public function op()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
