<?php

namespace DialInno\Jaal\Tests\Models;


use Illuminate\Database\Eloquent\Model;
use DialInno\Jaal\Api\Traits\SerializeModel;

class Skill extends Model
{
    use SerializeModel;

    public function users()
    {
        return $this->belongsToMany(User::class);
    }
}