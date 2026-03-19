<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'icon',
        'price',
        'short_service_detail',
        'description',
        'effective_date',
        'expiry_date',
        'is_active',
    ];

    public function getIconUrlAttribute()
    {
        if ($this->icon) {
            return asset('storage/' . $this->icon);
        }
        return null;
    }

    public function steps()
    {
        return $this->hasMany(ServiceStep::class)->orderBy('order');
    }

    public function submissions()
    {
        return $this->hasMany(ServiceSubmission::class);
    }
}
