<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaDocument extends Model
{
    protected $fillable = [
        'work_order_id',
        'ba_number',
        'ba_date',
        'template_id',
        'generated_by',
        'content_json',
        'pdf_path',
        'status',
    ];

    protected $casts = [
        'ba_date' => 'date:Y-m-d',
        'content_json' => 'array',
    ];

    public function workOrder()
    {
        return $this->belongsTo(WorkOrder::class);
    }

    public function template()
    {
        return $this->belongsTo(DocumentTemplate::class, 'template_id');
    }

    public function generator()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
