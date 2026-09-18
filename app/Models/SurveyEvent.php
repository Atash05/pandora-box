<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyEvent extends Model
{
    protected $fillable = [
        'survey_id',
        'action',
        'screen',
        'details',
        'phone_request_id',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
        ];
    }
}
