<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Chronic extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'chronic';
    protected $primaryKey = 'chronic_id';

    protected $fillable = [
        'procedure_id',
        'speciality_id',
        'refill_date',
        'compliance',
        'exercise',
        'clinical_goals',
        'annual_check_up',
        'nutrition_follow_up',
        'psychosocial',
        'specialist_review',
        'vitals_monitoring',
        'revenue',
        'remote_vital_monitor',
        'patient_id',
        'scheme_id',
        'last_visit',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'refill_date' => 'date',
        'last_visit' => 'date',
        'revenue' => 'decimal:2',
        'nutrition_follow_up' => 'boolean',
        'psychosocial' => 'boolean',
        'annual_check_up' => 'boolean',
        'specialist_review' => 'boolean',
     
    ];

    protected $dates = ['deleted_at'];
    public $timestamps = true;

    // Relationships
    public function patient()
    {
        return $this->belongsTo(Patient::class, 'patient_id')->withDefault();
    }

    public function scheme()
    {
        return $this->belongsTo(Scheme::class, 'scheme_id')->withDefault();
    }

    public function procedure()
    {
        return $this->belongsTo(Procedure::class, 'procedure_id')->withDefault();
    }

    public function speciality()
    {
        return $this->belongsTo(Specialist::class, 'speciality_id')->withDefault();
    }

    // Cache methods
    protected static function cacheKey($type): string
    {
        return "chronics_count_{$type}";
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

    // Model events
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('not_deleted', function ($builder) {
            $builder->whereNull('deleted_at');
        });

        static::restoring(function ($chronic) {
            static::clearCountCache();
        });

        static::deleting(function ($chronic) {
            static::clearCountCache();
            if ($chronic->isForceDeleting()) {
                // Add any force delete cleanup logic here
            }
        });

        static::created(function () {
            static::clearCountCache();
        });

        static::updated(function ($chronic) {
            if ($chronic->isDirty(['deleted_at'])) {
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
        // Add any pre-deletion checks here if needed
        return $this->forceDelete();
    }
}