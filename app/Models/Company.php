<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'registration_number',
        'contact_email',
        'status',
        'municipality_id',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Relationship: Municipality this company belongs to
     */
    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    /**
     * Relationship: Uploads for this company
     */
    public function uploads()
    {
        return $this->hasMany(Uploads::class);
    }

    /**
     * Relationship: User assignments for this company
     */
    public function assignments()
    {
        return $this->hasMany(UserAssignment::class);
    }

    /**
     * Relationship: Users assigned to this company
     */
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'user_assignments')
            ->withPivot('deadline_date', 'notes', 'municipality_id')
            ->withTimestamps();
    }

    /**
     * Scope: Filter companies accessible to a user
     */
    public function scopeAccessibleToUser($query, $userId)
    {
        return $query->whereHas('assignments', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope: Filter companies by municipality
     */
    public function scopeByMunicipality($query, $municipalityId)
    {
        return $query->where('municipality_id', $municipalityId);
    }

    /**
     * Check if company is assigned to any user for a specific date
     */
    public function isAssignedForDate($deadlineDate)
    {
        return $this->assignments()
            ->where('deadline_date', $deadlineDate)
            ->exists();
    }

    /**
     * Get the user assigned to this company for a specific date
     */
    public function getAssignedUserForDate($deadlineDate)
    {
        $assignment = $this->assignments()
            ->where('deadline_date', $deadlineDate)
            ->first();

        return $assignment ? $assignment->user : null;
    }
}
