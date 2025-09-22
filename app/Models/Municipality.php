<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Municipality extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'province',
        'code',
    ];

    /**
     * Relationship: Companies in this municipality
     */
    public function companies()
    {
        return $this->hasMany(Company::class);
    }

    /**
     * Relationship: Deadlines for this municipality
     */
    public function deadlines()
    {
        return $this->hasMany(MunicipalityDeadline::class);
    }

    /**
     * Relationship: User assignments for this municipality
     */
    public function assignments()
    {
        return $this->hasMany(UserAssignment::class);
    }

    /**
     * Relationship: Users assigned to this municipality
     */
    public function assignedUsers()
    {
        return $this->belongsToMany(User::class, 'user_assignments')
            ->withPivot('deadline_date', 'notes', 'company_id')
            ->withTimestamps();
    }

    /**
     * Scope: Filter municipalities accessible to a user
     */
    public function scopeAccessibleToUser($query, $userId)
    {
        return $query->whereHas('assignments', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Get companies that are not assigned to any user for a specific date
     */
    public function getUnassignedCompanies($deadlineDate)
    {
        $assignedCompanyIds = $this->assignments()
            ->where('deadline_date', $deadlineDate)
            ->pluck('company_id')
            ->filter()
            ->toArray();

        return $this->companies()
            ->whereNotIn('id', $assignedCompanyIds)
            ->get();
    }

    /**
     * Check if municipality has any assignments for a specific date
     */
    public function hasAssignmentsForDate($deadlineDate)
    {
        return $this->assignments()
            ->where('deadline_date', $deadlineDate)
            ->exists();
    }
}
