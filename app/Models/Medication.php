<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Medication extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'medication';
    protected $primaryKey = 'medication_id';

    protected $fillable = [
        'item_name',
        'composition',
        'brand',
        'formulation',
        'category',
    ];

    protected $dates = ['deleted_at'];
    public $incrementing = true;
    public $timestamps = true;

    // Relationships
    public function medicationUses()
    {
        return $this->hasMany(MedicationUse::class, 'medication_id');
    }

    public function activeMedicationUses()
    {
        return $this->hasMany(MedicationUse::class, 'medication_id')->whereNull('deleted_at');
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

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Cache methods
    protected static function cacheKey($type): string
    {
        return "medications_count_{$type}";
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

    // Model Events
    protected static function boot()
    {
        parent::boot();

        static::addGlobalScope('not_deleted', function ($builder) {
            $builder->whereNull('deleted_at');
        });

        static::restoring(function () {
            static::clearCountCache();
        });

        static::deleting(function () {
            static::clearCountCache();
        });

        static::created(function () {
            static::clearCountCache();
        });

        static::updated(function ($medication) {
            if ($medication->isDirty(['deleted_at'])) {
                static::clearCountCache();
            }
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
        if ($this->activeMedicationUses()->exists()) {
            throw new \Exception('Cannot force delete medication with active uses.');
        }
        return $this->forceDelete();
    }
}