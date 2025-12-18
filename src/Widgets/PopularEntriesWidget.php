<?php

namespace Mikomagni\SimpleLikes\Widgets;

use Illuminate\Support\Facades\Cache;
use Statamic\Widgets\VueComponent;
use Statamic\Widgets\Widget;
use Illuminate\Support\Facades\DB;
use Mikomagni\SimpleLikes\Models\SimpleLike;
use Statamic\Facades\Entry;

class PopularEntriesWidget extends Widget
{
    /**
     * Return the Vue component to render
     *
     * @return \Statamic\Widgets\VueComponent|null
     */
    public function component()
    {
        $config = config('simple-likes.widget', []);
        $limit = $config['popular_entries_limit'] ?? 5;
        $sortBy = $config['popular_entries_sort_by'] ?? 'total';

        // Cache widget data to reduce CP load
        $cacheTtl = config('simple-likes.cache.widget_popular_ttl', 300);
        $cacheKey = "simple_likes_widget_popular_{$limit}_{$sortBy}";
        $popularEntries = Cache::remember($cacheKey, $cacheTtl, function () use ($limit, $sortBy) {
            return $this->getPopularEntries($limit, $sortBy);
        });

        // Only show if there's data
        if ($popularEntries->count() === 0) {
            return null;
        }

        return VueComponent::render('SimpleLikesPopularEntries', [
            'entries' => $popularEntries->toArray(),
        ]);
    }

    /**
     * Get popular entries
     *
     * Terminology:
     * - "Preset" = The base number manually set in the field
     * - "Interactions" = Real clicks from actual users
     * - "Member" = Logged-in/authenticated users
     * - "Anonymous" = Guest users (tracked via IP hash)
     *
     * @param int $limit
     * @param string $sortBy 'total' for total likes (preset + interactions) or 'interactions' for real clicks only
     */
    private function getPopularEntries($limit = 5, $sortBy = 'total')
    {
        // Get all entries with likes breakdown by user type
        $entriesWithLikes = SimpleLike::select('entry_id', 'user_type', DB::raw('COUNT(*) as like_count'))
            ->groupBy('entry_id', 'user_type')
            ->get()
            ->groupBy('entry_id');

        // Convert to more usable format with clear naming
        $entriesData = $entriesWithLikes->map(function ($likes, $entryId) {
            $memberLikes = $likes->where('user_type', 'authenticated')->first()->like_count ?? 0;
            $anonymousLikes = $likes->where('user_type', 'guest')->first()->like_count ?? 0;

            return (object) [
                'interactions' => $memberLikes + $anonymousLikes,  // Total real clicks
                'member_likes' => $memberLikes,                     // Logged-in users
                'anonymous_likes' => $anonymousLikes                // Guest users
            ];
        });

        // Only load entries that have likes (avoid Entry::all())
        $entryIdsWithLikes = $entriesData->keys()->toArray();

        // Batch load all entries at once
        $entries = Entry::query()->whereIn('id', $entryIdsWithLikes)->get();

        // Calculate total likes for each entry and sort
        $entriesWithTotals = $entries->map(function ($entry) use ($entriesData) {
            $presetCount = (int) $entry->get('simple_likes', 0);
            $data = $entriesData[$entry->id()] ?? (object) ['interactions' => 0, 'member_likes' => 0, 'anonymous_likes' => 0];
            $totalLikes = $presetCount + $data->interactions;

            return [
                'entry_id' => $entry->id(),
                'title' => $entry->get('title'),
                'cp_url' => "/cp/collections/{$entry->collectionHandle()}/entries/{$entry->id()}",
                'collection' => $entry->collectionHandle(),

                // Clear naming
                'total_likes' => $totalLikes,                    // Preset + Interactions
                'preset_count' => $presetCount,                  // Manually set base value
                'interactions' => $data->interactions,           // Real clicks (member + anonymous)
                'member_likes' => $data->member_likes,           // Logged-in users
                'anonymous_likes' => $data->anonymous_likes,     // Guest users
            ];
        });

        // Sort based on configuration
        if ($sortBy === 'real' || $sortBy === 'interactions') {
            $entriesWithTotals = $entriesWithTotals->sortByDesc('interactions');
        } else {
            $entriesWithTotals = $entriesWithTotals->sortByDesc('total_likes');
        }

        $entriesWithTotals = $entriesWithTotals
            ->take($limit)
            ->values();

        return $entriesWithTotals;
    }
}
