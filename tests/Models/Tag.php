<?php

namespace DialInno\Jaal\Tests\Models;

use Illuminate\Database\Eloquent\Model;

class Tag extends Model
{
    public $timestamps = false;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['slug',];

    public function accounts()
    {
        return $this->morphedByMany(Account::class, 'taggable');
    }

    public function agents()
    {
        return $this->morphedByMany(Agent::class, 'taggable');
    }

    public function contacts()
    {
        return $this->morphedByMany(Contact::class, 'taggable');
    }
}
