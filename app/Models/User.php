<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'mobile',
        'department',
        'status',
        'password',

    ];

    protected $hidden = [
        'password',
        'remember_token',
        'jwt_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function roles()
    {
        return $this->belongsToMany(
            Role::class,
            'model_has_roles',
            'model_id',
            'role_id'
        )->wherePivot('model_type', self::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(
            Permission::class,
            'model_has_permissions',
            'model_id',
            'permission_id'
        )->wherePivot('model_type', self::class);
    }

    public function hasPermission(string $routeName): bool
    {
        if ($this->permissions->contains('route_name', $routeName)) {
            return true;
        }

        foreach ($this->roles as $role) {
            if ($role->permissions->contains('route_name', $routeName)) {
                return true;
            }
        }

        return false;
    }

}
