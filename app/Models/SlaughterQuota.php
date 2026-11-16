<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SlaughterQuota extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'quota',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date:Y-m-d',
            'quota' => 'integer',
        ];
    }

    /**
     * Get remaining quota on a specific date.
     */
    public static function getRemainingQuota(string|Carbon $date): int
    {
        $dateStr = $date instanceof Carbon ? $date->format('Y-m-d') : Carbon::parse($date)->format('Y-m-d');
        $record = static::where('date', $dateStr)->first();

        if (! $record) {
            return 0; // No quota configured = 0
        }

        // Count existing scheduled orders on that date (if orders table exists)
        $scheduledCount = \Illuminate\Support\Facades\Schema::hasTable('orders')
            ? (int) \App\Models\Order::whereDate('scheduled_at', $dateStr)
                ->whereNotIn('status', ['dibatalkan', 'kedaluwarsa'])
                ->count()
            : 0;

        return max(0, $record->quota - $scheduledCount);
    }
}

