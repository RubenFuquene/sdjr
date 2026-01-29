<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class SeederControl extends Model
{
    protected $table = 'seeder_control';

    protected $fillable = [
        'seeder_name',
        'version',
        'executed_at',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'executed_at' => 'datetime',
    ];

    /**
     * Check if seeder has been executed for given version
     */
    public static function hasBeenExecuted(string $seederName, string $version = '1.0'): bool
    {
        return self::where('seeder_name', $seederName)
            ->where('version', $version)
            ->exists();
    }

    /**
     * Mark seeder as executed
     */
    public static function markAsExecuted(string $seederName, string $version = '1.0', array $metadata = []): void
    {
        self::updateOrCreate(
            ['seeder_name' => $seederName],
            [
                'version' => $version,
                'executed_at' => Carbon::now(),
                'metadata' => $metadata,
            ]
        );
    }

    /**
     * Force reset seeder control (for re-seeding)
     */
    public static function resetSeeder(string $seederName): void
    {
        self::where('seeder_name', $seederName)->delete();
    }

    /**
     * Reset all seeders (for complete re-seed)
     */
    public static function resetAll(): void
    {
        self::truncate();
    }
}
