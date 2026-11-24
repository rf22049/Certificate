<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CertificateNumberFormat extends Model
{
    protected $fillable = ['name', 'fields', 'separator'];

    protected function casts(): array
    {
        return ['fields' => 'array'];
    }
}
