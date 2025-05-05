<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Diagnosis extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'diagnosis';
    protected $primaryKey = 'diagnosis_id'; // Fixed typo from 'diagnosis_id'

    protected $fillable = [
        'diagnosis_name',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
       
    ];

    public $timestamps = true;

    // Relationships (add your actual relationships here)
    public function patients()
    {
        return $this->belongsToMany(Patient::class, 'patient_diagnosis', 'diagnosis_id', 'patient_id');
    }

    // Relationships
    public function physiotherapies()
    {
        return $this->hasMany(Physiotherapy::class, 'procedure_id', 'id');
    }

    public function patientsWithTrashed()
    {
        return $this->belongsToMany(Patient::class, 'patient_diagnosis', 'diagnosis_id', 'patient_id')->withTrashed();
    }

    public function trashedPatients()
    {
        return $this->belongsToMany(Patient::class, 'patient_diagnosis', 'diagnosis_id', 'patient_id')->onlyTrashed();
    }

    // Accessors
    public function getIsActiveAttribute()
    {
        return is_null($this->deleted_at);
    }

    // Cache methods
    protected static function cacheKey($type): string
    {
        return "diagnoses_count_{$type}";
    }

    public static function cachedActiveCount(): int
    {
        return Cache::remember(
            static::cacheKey('active'),
            now()->addMinutes(30),
            fn() => static::withoutGlobalScopes()->whereNull('deleted_at')->count()
        );
    }

    public static function cachedTrashedCount(): int
    {
        return Cache::remember(
            static::cacheKey('trashed'),
            now()->addMinutes(30),
            fn() => static::withoutGlobalScopes()->onlyTrashed()->count()
        );
    }

    public static function cachedTotalCount(): int
    {
        return Cache::remember(
            static::cacheKey('total'),
            now()->addMinutes(30),
            fn() => static::withoutGlobalScopes()->withTrashed()->count()
        );
    }

    public static function clearCountCache(): void
    {
        Cache::forget(static::cacheKey('active'));
        Cache::forget(static::cacheKey('trashed'));
        Cache::forget(static::cacheKey('total'));
    }

    // Model events
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('not_deleted', function ($builder) {
            $builder->whereNull('deleted_at');
        });

        static::deleting(function ($diagnosis) {
            if ($diagnosis->isForceDeleting()) {
                // Handle any force delete operations here
                // Example: $diagnosis->patients()->detach();
            }
            static::clearCountCache();
        });

        static::restoring(function ($diagnosis) {
            // Handle any restore operations here
            static::clearCountCache();
        });

        static::created(function () {
            static::clearCountCache();
        });

        static::updated(function ($diagnosis) {
            if ($diagnosis->isDirty('deleted_at')) {
                static::clearCountCache();
            }
        });

        static::saved(function () {
            static::clearCountCache();
        });
    }

    // Custom queries
    public static function active()
    {
        return static::whereNull('deleted_at');
    }

    public static function inactive()
    {
        return static::onlyTrashed();
    }

    // Force delete with protection
    public function forceDeleteWithCheck()
    {
        // Add any pre-deletion checks here
        // Example: if ($this->patients()->exists()) {
        //     throw new \Exception("Cannot force delete diagnosis with associated patients");
        // }

        return $this->forceDelete();
    }
}