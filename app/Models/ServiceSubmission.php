<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceSubmission extends Model
{
    protected $fillable = [
        'service_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function getValue($key)
    {
        return $this->data[$key] ?? null;
    }
}
