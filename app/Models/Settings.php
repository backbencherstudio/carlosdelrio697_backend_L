<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    protected $fillable = [
        'user_id',
        'company_name',
        'support_mail',
        'email_on_new_orders',
        'email_on_compliance_flags',
        'email_on_risk_allert'
    ];

    protected function casts(): array
    {
        return [
            'email_on_new_orders' => 'boolean',
            'email_on_compliance_flags' => 'boolean',
            'email_on_risk_allert' => 'boolean',
        ];
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
