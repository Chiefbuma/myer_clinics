<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Route extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'route';
    protected $primaryKey = 'route_id';

    protected $fillable = [
        'route_name',
        'latitude',
        'longitude',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'deleted_at' => 'datetime',
        // Removed 'latitude' => 'decimal:9,6' and 'longitude' => 'decimal:9,6' due to casting issue
        // Treating latitude and longitude as floats instead
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    protected $dates = ['deleted_at'];
    public $incrementing = true;
    public $timestamps = true;

    // Cache methods
    protected static function cacheKey($type): string
    {
        return "routes_count_{$type}";
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

        static::deleting(function ($route) {
            static::clearCountCache();
        });

        static::restoring(function ($route) {
            static::clearCountCache();
        });

        static::created(function () {
            static::clearCountCache();
        });

        static::updated(function ($route) {
            if ($route->isDirty(['deleted_at'])) {
                static::clearCountCache();
            }
        });

        static::saved(function () {
            static::clearCountCache();
        });
    }
}