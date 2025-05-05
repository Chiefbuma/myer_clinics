<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Physiotherapy extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'physiotherapy';
    protected $primaryKey = 'id'; // Use 'id' as the primary key
    public $incrementing = true; // Ensure the primary key is auto-incrementing
    public $timestamps = true;
    protected $dates = ['deleted_at']; // Ensure soft delete column is treated as a date

    protected $fillable = [
        'patient_id',
        'scheme_id',
        'visit_date',
        'pain_level',
        'mobility_score',
        'range_of_motion',
        'strength',
        'balance',
        'walking_ability',
        'posture_assessment',
        'exercise_type',
        'frequency_per_week',
        'duration_per_session',
        'intensity',
        'pain_level_before_exercise',
        'pain_level_after_exercise',
        'fatigue_level_before_exercise',
        'fatigue_level_after_exercise',
        'post_exercise_recovery_time',
        'functional_independence',
        'joint_swelling',
        'muscle_spasms',
        'progress',
        'treatment',
        'challenges',
        'adjustments_made',
        'calcium_levels',
        'phosphorous_levels',
        'vit_d_levels',
        'cholesterol_levels',
        'iron_levels',
        'heart_rate',
        'blood_pressure_systolic',
        'blood_pressure_diastolic',
        'oxygen_saturation',
        'hydration_level',
        'sleep_quality',
        'stress_level',
        'medication_usage',
        'therapist_notes',
        'revenue',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'revenue' => 'decimal:2',
        'pain_level' => 'integer',
        'mobility_score' => 'integer',
        'range_of_motion' => 'decimal:1',
        'strength' => 'integer',
        'balance' => 'integer',
        'walking_ability' => 'integer',
        'frequency_per_week' => 'integer',
        'duration_per_session' => 'integer',
        'heart_rate' => 'integer',
        'blood_pressure_systolic' => 'integer',
        'blood_pressure_diastolic' => 'integer',
        'oxygen_saturation' => 'integer',
        'sleep_quality' => 'integer',
        'stress_level' => 'integer',
        'calcium_levels' => 'decimal:2',
        'phosphorous_levels' => 'decimal:2',
        'vit_d_levels' => 'decimal:2',
        'cholesterol_levels' => 'decimal:2',
        'iron_levels' => 'decimal:2',
        'hydration_level' => 'decimal:1',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeInactive($query)
    {
        return $query->whereNotNull('deleted_at');
    }

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('visit_date', [$startDate, $endDate]);
    }

    public function scopeWithHighPainLevel($query, $threshold = 7)
    {
        return $query->where('pain_level', '>', $threshold);
    }

    // Cache methods
    protected static function cacheKey($type): string
    {
        return "physiotherapy_count_{$type}";
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

        // Add a global scope to exclude soft-deleted records by default
        static::addGlobalScope('not_deleted', function ($builder) {
            $builder->whereNull('deleted_at');
        });

        static::restoring(function ($physiotherapy) {
            static::clearCountCache();
        });

        static::deleting(function ($physiotherapy) {
            static::clearCountCache();
        });

        static::created(function () {
            static::clearCountCache();
        });

        static::updated(function ($physiotherapy) {
            if ($physiotherapy->isDirty(['deleted_at'])) {
                static::clearCountCache();
            }
        });

        static::saved(function () {
            static::clearCountCache();
        });
    }

    // Helper Methods
    public function isActive()
    {
        return is_null($this->deleted_at);
    }

    public function isDeleted()
    {
        return !is_null($this->deleted_at);
    }

    public function bloodPressureStatus()
    {
        if (!$this->blood_pressure_systolic || !$this->blood_pressure_diastolic) {
            return null;
        }

        if ($this->blood_pressure_systolic >= 140 || $this->blood_pressure_diastolic >= 90) {
            return 'High';
        } elseif ($this->blood_pressure_systolic <= 90 || $this->blood_pressure_diastolic <= 60) {
            return 'Low';
        }
        return 'Normal';
    }

    // Custom Force Delete with Protection
    public function safeForceDelete()
    {
        return $this->forceDelete();
    }
}