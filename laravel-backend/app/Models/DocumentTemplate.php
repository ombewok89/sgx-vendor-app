<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    protected $fillable = [
        'name',
        'code',
        'header_html',
        'footer_html',
        'body_template',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function baDocuments()
    {
        return $this->hasMany(BaDocument::class, 'template_id');
    }
}
