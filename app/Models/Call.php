<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Call extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'calls';
    protected $primaryKey = 'call_id';

    protected $fillable = [
        'patient_id',
        'call_result',
        'call_date',
    ];

    protected $casts = [
        'call_date' => 'datetime',
    ];

    protected $dates = ['deleted_at'];
    public $timestamps = true;

    // Relationships
    public function callResult()
    {
        return $this->belongsTo(CallResult::class, 'Call_results_id', 'Call_results_id');
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
        return "calls_count_{$type}";
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

        static::restoring(function ($call) {
            static::clearCountCache();
        });

        static::deleting(function ($call) {
            static::clearCountCache();
        });

        static::created(function () {
            static::clearCountCache();
        });

        static::updated(function ($call) {
            if ($call->isDirty(['deleted_at'])) {
                static::clearCountCache();
            }
        });

        static::saved(function () {
            static::clearCountCache();
        });
    }
}