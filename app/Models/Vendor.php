<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'code',
        'name',
        'contact_person',
        'phone',
        'email',
        'logo_url',
        'banner_url',
        'npwp',
        'website',
        'address',
        'ba_template_id',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'ba_template_id' => 'integer',
    ];

    public function baTemplate()
    {
        return $this->belongsTo(DocumentTemplate::class, 'ba_template_id');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function workOrders()
    {
        return $this->hasMany(WorkOrder::class);
    }
}
