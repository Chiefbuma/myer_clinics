<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class MedicationUse extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'medication_use';
    protected $primaryKey = 'medication_use_id';

    protected $fillable = [
        'days_supplied',
        'no_pills_dispensed',
        'frequency',
        'medication_id',
        'patient_id',
        'visit_date',
        'scheme_id',
        'procedure_id',
        'speciality_id',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'visit_date' => 'date',
    ];

    protected $dates = ['deleted_at'];
    public $incrementing = true;
    public $timestamps = true;

    // Relationships
    public function medication()
    {
        return $this->belongsTo(Medication::class, 'medication_id');
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id');
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id');
    }

    public function procedure()
    {
        return $this->belongsTo(Procedure::class, 'procedure_id');
    }

    public function speciality()
    {
        return $this->belongsTo(Specialist::class, 'speciality_id');
    }

    // Cache methods
    protected static function cacheKey($type): string
    {
        return "medication_use_count_{$type}";
    }

    public static function cachedActiveCount(): int
    {
        return Cache::remember(
            static::cacheKey('active'),
            now()->addMinutes(30),
            fn() => static::withoutGlobalScopes()
                ->whereNull('deleted_at')
                ->count()
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

    // Scopes
    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeInactive($query)
    {
        return $query->whereNotNull('deleted_at');
    }

    public function scopeByMedication($query, $medicationId)
    {
        return $query->where('medication_id', $medicationId);
    }

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('visit_date', [$startDate, $endDate]);
    }

    // Model Events
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('not_deleted', function ($builder) {
            $builder->whereNull('deleted_at');
        });

        static::restoring(function ($medicationUse) {
            static::clearCountCache();
        });

        static::deleting(function ($medicationUse) {
            static::clearCountCache();
            if ($medicationUse->isForceDeleting()) {
                // Add any force delete cleanup logic here
            }
        });

        static::created(function () {
            static::clearCountCache();
        });

        static::updated(function ($medicationUse) {
            if ($medicationUse->isDirty(['deleted_at'])) {
                static::clearCountCache();
            }
        });

        static::saved(function () {
            static::clearCountCache();
        });
    }

    // Helper Methods
    public function isActive(): bool
    {
        return is_null($this->deleted_at);
    }

    public function isDeleted(): bool
    {
        return !is_null($this->deleted_at);
    }

    // Custom Force Delete with Protection
    public function safeForceDelete()
    {
        // Add any protection logic before force deleting
        return $this->forceDelete();
    }

    // Additional Business Logic
    public function calculateDaysRemaining()
    {
        if ($this->no_pills_dispensed && $this->frequency) {
            $frequencyMap = [
                'daily' => 1,
                'weekly' => 7,
                'monthly' => 30,
            ];
            $daysPerDose = $frequencyMap[$this->frequency] ?? 1;
            return floor($this->no_pills_dispensed * $daysPerDose);
        }

        return null;
    }
}