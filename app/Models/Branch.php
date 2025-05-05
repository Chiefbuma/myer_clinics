<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Branch extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'branch';
    protected $primaryKey = 'branch_id';

    protected $fillable = [
        'branch_name',
        'created_at',
        'updated_at',
    ];

    protected $dates = ['deleted_at'];
    public $timestamps = true;

    public function patients()
    {
        return $this->hasMany(Patient::class, 'branch_id', 'branch_id');
    }

    // Cache methods
    protected static function cacheKey($type): string
    {
        return "branches_count_{$type}";
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

        static::restoring(function ($branch) {
            static::clearCountCache();
        });

        static::deleting(function ($branch) {
            static::clearCountCache();
        });

        static::created(function () {
            static::clearCountCache();
        });

        static::updated(function ($branch) {
            if ($branch->isDirty(['deleted_at'])) {
                static::clearCountCache();
            }
        });

        static::saved(function () {
            static::clearCountCache();
        });
    }
}