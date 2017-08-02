<?php

namespace DialInno\Jaal\Tests\Models;

use Illuminate\Notifications\Notifiable;
use DialInno\Jaal\Api\Traits\SerializeModel;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable
{
    //use Notifiable;
    use SerializeModel;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token', 'deleted_at',
    ];

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class);
    }
}
