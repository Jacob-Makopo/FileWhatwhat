<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Uploads extends Model
{
    use HasFactory;

    // Fillable fields
    protected $fillable = [
        'company_id',
        'municipality_id',
        'reference',
        'status',
        'submitted_at',
        'original_file_path',
        'original_file_names',
        'workings_file_path',
        'workings_file_name',
        'systems_import_file_path',
        'systems_import_file_name',
        'extracted_dates',
        'system_import_date',
    ];

    // Update the casts array:
    protected $casts = [
        'submitted_at' => 'datetime',
        'extracted_dates' => 'array',
        'system_import_date' => 'datetime',
        'original_file_path' => 'array',
        'original_file_names' => 'array',
    ];

    /**
     * Relationship: Company this upload belongs to
     */
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Relationship: Municipality this upload belongs to
     */
    public function municipality()
    {
        return $this->belongsTo(Municipality::class);
    }

    // Accessors for file URLs
    public function getOriginalFileUrlsAttribute()
    {
        $paths = $this->original_file_path;

        return array_map(fn($path) => $path ? asset('storage/' . $path) : null, (array) $paths);
    }

    public function getWorkingsFileUrlAttribute()
    {
        return $this->workings_file_path ? asset('storage/' . $this->workings_file_path) : null;
    }

    public function getSystemsImportFileUrlAttribute()
    {
        return $this->systems_import_file_path ? asset('storage/' . $this->systems_import_file_path) : null;
    }

    protected function setExtractedDatesAttribute($value)
    {
        $this->attributes['extracted_dates'] = json_encode((array) $value);
    }

    public function getWorkingsFileNameAttribute()
    {
        return $this->attributes['workings_file_name'] ?? null;
    }

    public function getSystemsImportFileNameAttribute()
    {
        return $this->attributes['systems_import_file_name'] ?? null;
    }

    public function getOriginalFileNamesAttribute($value)
    {
        if (is_array($value)) {
            return $value;
        }

        return json_decode($value, true) ?? [];
    }

    // And the mutator for original_file_names:
    public function setOriginalFileNamesAttribute($value)
    {
        $this->attributes['original_file_names'] = json_encode((array) $value);
    }

    /**
     * Scope: Filter uploads accessible to a user based on assignments
     */
    public function scopeAccessibleToUser($query, $userId)
    {
        return $query->whereHas('company.assignments', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->orWhereHas('municipality.assignments', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        });
    }

    /**
     * Scope: Filter uploads by municipality
     */
    public function scopeByMunicipality($query, $municipalityId)
    {
        return $query->where('municipality_id', $municipalityId);
    }

    /**
     * Scope: Filter uploads by company
     */
    public function scopeByCompany($query, $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    /**
     * Scope: Filter uploads by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Check if upload is accessible to a specific user
     */
    public function isAccessibleToUser($userId)
    {
        return $this->company->assignments()->where('user_id', $userId)->exists() ||
            $this->municipality->assignments()->where('user_id', $userId)->exists();
    }
}
