<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use App\Models\Patient;

class Cohort extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cohort';
    protected $primaryKey = 'cohort_id';

    protected $fillable = [
        'cohort_name',
        'team_lead',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public $timestamps = true;

    // Relationships
    public function patients()
    {
        return $this->hasMany(Patient::class, 'cohort_id', 'cohort_id');
    }

    public function activePatients()
    {
        return $this->hasMany(Patient::class, 'cohort_id', 'cohort_id')
                    ->whereNull('deleted_at');
    }

    public function trashedPatients()
    {
        return $this->hasMany(Patient::class, 'cohort_id', 'cohort_id')
                    ->onlyTrashed();
    }

    // Accessors
    public function getIsActiveAttribute()
    {
        return is_null($this->deleted_at);
    }

    // Cache methods
    protected static function cacheKey($type): string
    {
        return "cohorts_count_{$type}";
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

        static::deleting(function ($cohort) {
            if ($cohort->isForceDeleting()) {
                $cohort->patients()->forceDelete();
            } else {
                $cohort->patients()->delete();
            }
            static::clearCountCache();
        });

        static::restoring(function ($cohort) {
            $cohort->patients()->withTrashed()->restore();
            static::clearCountCache();
        });

        static::created(function () {
            static::clearCountCache();
        });

        static::updated(function ($cohort) {
            if ($cohort->isDirty('deleted_at')) {
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

    public function forceDeleteWithCheck()
    {
        if ($this->patients()->exists()) {
            throw new \Exception("Cannot force delete cohort with active patients");
        }

        return $this->forceDelete();
    }
}
