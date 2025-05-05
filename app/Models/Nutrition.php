<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Nutrition extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'nutrition';
    protected $primaryKey = 'nutrition_id';

    protected $fillable = [
        'scheme_id',
        'patient_id',
        'last_visit',
        'next_review',
        'muscle_mass',
        'bone_mass',
        'weight',
        'BMI',
        'total_body_fat',
        'visceral_fat',
        'weight_remarks',
        'physical_activity',
        'meal_plan_set_up',
        'nutrition_adherence',
        'nutrition_assessment_remarks',
        'revenue',
        'visit_date',
    ];

    protected $casts = [
        'last_visit' => 'date',
        'next_review' => 'date',
        'visit_date' => 'date',
        'revenue' => 'decimal:2',
        'muscle_mass' => 'decimal:2',
        'bone_mass' => 'decimal:2',
        'weight' => 'decimal:2',
        'BMI' => 'decimal:2',
        'total_body_fat' => 'decimal:2',
        'visceral_fat' => 'decimal:2',
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

    // Cache methods
    protected static function cacheKey($type): string
    {
        return "nutrition_count_{$type}";
    }

    public static function cachedActiveCount(): int
    {
        return Cache::remember(
            static::cacheKey('active'),
            now()->addMinutes(30),
            fn() => static::query()->whereNull('deleted_at')->count()
        );
    }

    public static function cachedTrashedCount(): int
    {
        return Cache::remember(
            static::cacheKey('trashed'),
            now()->addMinutes(30),
            fn() => static::query()->onlyTrashed()->count()
        );
    }

    public static function cachedTotalCount(): int
    {
        return Cache::remember(
            static::cacheKey('total'),
            now()->addMinutes(30),
            fn() => static::query()->withTrashed()->count()
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
            $builder->whereNull('nutrition.deleted_at');
        });

        static::created(function () {
            static::clearCountCache();
        });

        static::updated(function ($nutrition) {
            if ($nutrition->isDirty(['deleted_at'])) {
                static::clearCountCache();
            }
        });

        static::deleting(function ($nutrition) {
            static::clearCountCache();
            if ($nutrition->isForceDeleting()) {
                // Add any force delete cleanup logic here
            }
        });

        static::restoring(function () {
            static::clearCountCache();
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

    public function safeForceDelete()
    {
        // Add any pre-deletion checks here if needed
        return $this->forceDelete();
    }
}