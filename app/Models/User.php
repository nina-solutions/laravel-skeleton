<?php

namespace FairHub\Models;

use Illuminate\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

class User extends HubModel implements AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use Authenticatable, Authorizable, CanResetPassword;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'users';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'email', 'password'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = ['password', 'remember_token'];

    public function isSuper(){
        return $this->level < 2;
    }

    public function isAdmin(){
        return $this->level < 3;
    }

    public function contexts(){
        return $this->belongsToMany('FairHub\Models\Context')->withPivot('role_id')->withTimestamps();
    }

    public function roles(){
        return $this->belongsToMany('FairHub\Models\Role', 'context_user', 'role_id', 'user_id')->withPivot('context_id')->withTimestamps();
    }
}
