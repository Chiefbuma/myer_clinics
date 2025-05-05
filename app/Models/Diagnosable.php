<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Diagnosable extends Model
{
    use HasFactory;

    protected $table = 'diagnosables';

    protected $fillable = [
        'diagnosis_id',
        'diagnosable_type',
        'diagnosable_id',
        'type',
        'notes',
    ];

    public function diagnosable()
    {
        return $this->morphTo();
    }

    public function diagnosis()
    {
        return $this->belongsTo(Diagnosis::class);
    }

    protected static function cacheKey(string $suffix): string
    {
        return sprintf('%s_%s', static::class, $suffix);
    }

    public static function cachedActiveCount(): int
    {
        return Cache::remember(
            static::cacheKey('active'),
            now()->addMinutes(30),
            fn() => static::withoutGlobalScopes()->whereNull('deleted_at')->count()
        );
    }
}
