<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentTemplate extends Model
{
    protected $fillable = [
        'name',
        'code',
        'logo_url',
        'header_image_url',
        'background_image_url',
        'footer_image_url',
        'header_html',
        'footer_html',
        'body_template',
        'table_config_json',
        'signatories_json',
        'signatory_first_party_name',
        'signatory_first_party_role',
        'signatory_second_party_name',
        'signatory_second_party_role',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'signatories_json' => 'array',
        'table_config_json' => 'array',
    ];

    public function baDocuments()
    {
        return $this->hasMany(BaDocument::class, 'template_id');
    }
}
