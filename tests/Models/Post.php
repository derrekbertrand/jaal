<?php

namespace DialInno\Jaal\Tests\Models;

use Illuminate\Database\Eloquent\Model;
use DialInno\Jaal\Core\Api\Traits\SerializeModel;


class Post extends Model
{
    use SerializeModel;

    public function op()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
