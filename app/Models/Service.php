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
        'is_active',
    ];

    public function steps()
    {
        return $this->hasMany(ServiceStep::class)->orderBy('order');
    }

    public function submissions()
    {
        return $this->hasMany(ServiceSubmission::class);
    }
}
