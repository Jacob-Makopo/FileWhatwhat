<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'employee_number',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Get the user's permissions for Inertia
     */
    public function toArray()
    {
        $array = parent::toArray();

        // Include permissions in user object
        $array['permissions'] = $this->getAllPermissions()->pluck('name');
        $array['roles'] = $this->getRoleNames();

        return $array;
    }

    /**
     * Relationship: User assignments to municipalities and companies
     */
    public function assignments()
    {
        return $this->hasMany(UserAssignment::class);
    }

    /**
     * Relationship: Municipalities assigned to this user
     */
    public function municipalities()
    {
        return $this->belongsToMany(Municipality::class, 'user_assignments')
            ->withPivot('deadline_date', 'notes', 'company_id')
            ->withTimestamps();
    }

    /**
     * Relationship: Companies assigned to this user
     */
    public function companies()
    {
        return $this->belongsToMany(Company::class, 'user_assignments')
            ->withPivot('deadline_date', 'notes', 'municipality_id')
            ->withTimestamps();
    }

    /**
     * Scope: Filter users by assigned municipalities
     */
    public function scopeWithMunicipalityAccess($query, $municipalityId)
    {
        return $query->whereHas('assignments', function ($q) use ($municipalityId) {
            $q->where('municipality_id', $municipalityId);
        });
    }

    /**
     * Check if user has access to a specific municipality
     */
    public function hasMunicipalityAccess($municipalityId)
    {
        return $this->assignments()->where('municipality_id', $municipalityId)->exists();
    }

    /**
     * Check if user has access to a specific company
     */
    public function hasCompanyAccess($companyId)
    {
        return $this->assignments()->where('company_id', $companyId)->exists();
    }
}
