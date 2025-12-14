<?php

namespace Mikomagni\SimpleLikes\Models;

use Illuminate\Database\Eloquent\Model;
use Statamic\Facades\Entry;

class SimpleLike extends Model
{
    protected $table = 'simple_likes';

    public function getConnectionName()
    {
        return config('simple-likes.connection') ?? parent::getConnectionName();
    }

    protected $fillable = [
        'entry_id',
        'user_id',
        'user_type',
        'ip_hash'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function scopeForEntry($query, $entryId)
    {
        return $query->where('entry_id', $entryId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeAuthenticated($query)
    {
        return $query->where('user_type', 'authenticated');
    }

    public function scopeGuests($query)
    {
        return $query->where('user_type', 'guest');
    }

    public static function getTotalLikesForEntry($entryId)
    {
        return static::forEntry($entryId)->count();
    }

    public static function getUserHasLiked($entryId, $userId)
    {
        return static::forEntry($entryId)->forUser($userId)->exists();
    }

    public static function getGlobalStats(bool $includeTimeRanges = false, ?array $precomputedEntryIds = null): array
    {
        $aggregates = static::query()
            ->selectRaw('COUNT(*) as total')
            ->selectRaw('COUNT(DISTINCT entry_id) as total_entries')
            ->selectRaw("SUM(CASE WHEN DATE(created_at) = ? THEN 1 ELSE 0 END) as today_likes", [today()->format('Y-m-d')])
            ->when($includeTimeRanges, function ($query) {
                $query->selectRaw("SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as week_likes", [now()->subWeek()])
                      ->selectRaw("SUM(CASE WHEN created_at >= ? THEN 1 ELSE 0 END) as month_likes", [now()->subMonth()]);
            })
            ->first();

        $entryIdsWithLikes = $precomputedEntryIds ?? static::distinct('entry_id')->pluck('entry_id')->toArray();
        $entries = Entry::query()->whereIn('id', $entryIdsWithLikes)->get();
        $totalStartingCount = $entries->sum(fn ($entry) => (int) $entry->get('simple_likes', 0));

        $stats = [
            'total_likes' => $totalStartingCount + (int) $aggregates->total,
            'total_entries' => (int) $aggregates->total_entries,
            'today_likes' => (int) $aggregates->today_likes,
        ];

        if ($includeTimeRanges) {
            $stats['week_likes'] = (int) $aggregates->week_likes;
            $stats['month_likes'] = (int) $aggregates->month_likes;
        }

        return $stats;
    }
}
