<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $fillable = [
        'name', 'email', 'phone', 'company', 'website', 'linkedin_url',
        'source', 'status', 'company_size', 'industry', 'location',
        'enrichment_data', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'enrichment_data' => 'array',
        ];
    }
}
