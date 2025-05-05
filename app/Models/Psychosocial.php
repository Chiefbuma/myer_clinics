<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Psychosocial extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'psychosocial';
    protected $primaryKey = 'id';

    protected $fillable = [
        'patient_id',
        'procedure_id',
        'speciality_id',
        'last_visit',
        'next_review',
        'educational_level',
        'career_business',
        'marital_status',
        'relationship_status',
        'primary_relationship_status',
        'ability_to_enjoy_leisure_activities',
        'spirituality',
        'level_of_self_esteem',
        'sex_life',
        'ability_to_cope_recover_disappointments',
        'rate_of_personal_development_growth',
        'achievement_of_balance_in_life',
        'social_support_system',
        'substance_use',
        'substance_used',
        'assessment_remarks',
        'revenue',
        'scheme_id',
        'visit_date',
        'deleted_at',
    ];

    protected $casts = [
        'last_visit' => 'date',
        'next_review' => 'date',
        'visit_date' => 'date',
        'revenue' => 'integer',
        'deleted_at' => 'datetime',
        'substance_use' => 'string', // Changed to string since it's a select field with options like "Occasional", "Monthly Use", etc.
        'substance_used' => 'string', // Single-select field, so cast as string
    ];

    protected $dates = ['deleted_at'];
    public $timestamps = true;

    // Relationships
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
        return $this->belongsTo(Procedure::class ,'procedure_id');
    }

    public function speciality()
    {
        return $this->belongsTo(Specialist::class,'speciality_id');
    }

    // Cache methods
    protected static function cacheKey($type): string
    {
        return "psychosocial_count_{$type}";
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

        static::restoring(function ($psychosocial) {
            static::clearCountCache();
        });

        static::deleting(function ($psychosocial) {
            static::clearCountCache();
        });

        static::created(function () {
            static::clearCountCache();
        });

        static::updated(function ($psychosocial) {
            if ($psychosocial->isDirty(['deleted_at'])) {
                static::clearCountCache();
            }
        });

        static::saved(function () {
            static::clearCountCache();
        });
    }
}