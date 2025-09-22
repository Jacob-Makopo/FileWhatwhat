<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAssignment extends Model
{
    protected $table = 'user_assignments'; // Explicitly set table name

    protected $fillable = [
        'user_id',
        'municipality_id',
        'company_id',
        'deadline_date',
        'notes'
    ];

    protected $casts = [
        'deadline_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }
}
