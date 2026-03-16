<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_id',
        'amount',
        'transaction_id',
        'status',
        'customer_email',
        'customer_name',
    ];
}
