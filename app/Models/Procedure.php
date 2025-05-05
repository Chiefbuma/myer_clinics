<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Procedure extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'procedures';
    protected $primaryKey = 'procedure_id';

    protected $fillable = [
        'procedure_name',
        'patient_id',
        'created_at',
        'updated_at',
    ];

    protected $dates = ['deleted_at'];
    public $incrementing = true;
    public $timestamps = true;

    // Relationships

    // Cache methods
    protected static function cacheKey($type): string
    {
        return "procedures_count_{$type}";
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

    // Model Events
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('not_deleted', function ($builder) {
            $builder->whereNull('deleted_at');
        });

        static::deleting(function ($procedure) {
            static::clearCountCache();
        });

        static::restoring(function ($procedure) {
            static::clearCountCache();
        });

        static::created(function () {
            static::clearCountCache();
        });

        static::updated(function ($procedure) {
            if ($procedure->isDirty(['deleted_at'])) {
                static::clearCountCache();
            }
        });

        static::saved(function () {
            static::clearCountCache();
        });
    }
}