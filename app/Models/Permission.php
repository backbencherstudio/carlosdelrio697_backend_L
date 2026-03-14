<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'route_name',
        'permission_name',
        'permission_type',
        'group_name',
        'guard_name'
    ];
}
