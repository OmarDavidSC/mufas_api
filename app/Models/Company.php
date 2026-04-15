<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use SoftDeletes;
    protected $table = 'companies';
    protected $fillable = [
        'id',
        'name',
        'host',
        'favicon_id',
        'logo_id',
        'create_account',
        'status',
        'terms_conditions',
        'privacy_policies',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_company_role')
            ->withPivot('role_id')
            ->withTimestamps();
    }

    public function roles()
    {
        return $this->hasManyThrough(Role::class, 'user_company_role', 'company_id', 'role_id');
    }
}
