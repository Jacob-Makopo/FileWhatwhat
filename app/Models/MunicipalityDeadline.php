<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MunicipalityDeadline extends Model
{
    use HasFactory;

    protected $fillable = [
        'municipality_id',
        'deadline_date',
        'notes',
    ];

    protected $casts = [
        'deadline_date' => 'date',
    ];

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }
}
