<?php

namespace Mikomagni\SimpleLikes\Widgets;

use Illuminate\Support\Facades\Cache;
use Statamic\Widgets\VueComponent;
use Statamic\Widgets\Widget;
use Mikomagni\SimpleLikes\Models\SimpleLike;
use Statamic\Facades\Entry;

class OverviewWidget extends Widget
{
    /**
     * Return the Vue component to render
     *
     * @return \Statamic\Widgets\VueComponent
     */
    public function component()
    {
        // Cache widget data to reduce CP load
        $cacheTtl = config('simple-likes.cache.widget_overview_ttl', 300);
        $overview = Cache::remember('simple_likes_widget_overview', $cacheTtl, function () {
            return $this->getOverview();
        });

        return VueComponent::render('SimpleLikesOverview', [
            'overview' => $overview,
        ]);
    }

    /**
     * Get overview statistics
     *
     * Terminology:
     * - "Preset" = The base number manually set in the field (fake/seed likes)
     * - "Interactions" = Real clicks from actual users (authenticated + anonymous)
     * - "Member" = Logged-in/authenticated users
     * - "Anonymous" = Guest users (tracked via IP hash)
     */
    private function getOverview()
    {
        // Get entry IDs that have real interactions in the database
        $entryIdsWithLikes = SimpleLike::distinct('entry_id')->pluck('entry_id');

        // Batch load all entries at once
        $entries = Entry::query()->whereIn('id', $entryIdsWithLikes->toArray())->get();

        // Calculate Boost Counts only for entries that have interactions
        $presetCount = $entries->sum(fn ($entry) => (int) $entry->get('simple_likes', 0));

        // Real interactions from actual users
        $totalInteractions = SimpleLike::count();
        $memberLikes = SimpleLike::authenticated()->count();
        $anonymousLikes = SimpleLike::guests()->count();

        // Combined total (preset + real interactions)
        $totalLikes = $presetCount + $totalInteractions;
        $totalUniqueEntries = $entryIdsWithLikes->count();

        // Calculate percentages (whole numbers for cleaner UI display)
        if ($totalLikes > 0) {
            $interactionsPercent = round(($totalInteractions / $totalLikes) * 100);
            $presetPercent = round(($presetCount / $totalLikes) * 100);
        } else {
            $interactionsPercent = 0;
            $presetPercent = 0;
        }

        if ($totalInteractions > 0) {
            $memberPercent = round(($memberLikes / $totalInteractions) * 100);
            $anonymousPercent = round(($anonymousLikes / $totalInteractions) * 100);
        } else {
            $memberPercent = 0;
            $anonymousPercent = 0;
        }

        // Time-based likes (only real interactions)
        $todayLikes = SimpleLike::whereDate('created_at', today())->count();
        $weekLikes = SimpleLike::where('created_at', '>=', now()->startOfWeek())->count();

        return [
            // Totals
            'total_likes' => $totalLikes,
            'total_entries' => $totalUniqueEntries,

            // Preset (manually set base values)
            'preset_count' => $presetCount,
            'preset_percent' => $presetPercent,

            // Real Interactions (actual clicks)
            'interactions_count' => $totalInteractions,
            'interactions_percent' => $interactionsPercent,

            // Breakdown of interactions
            'member_likes' => $memberLikes,           // Logged-in users
            'member_percent' => $memberPercent,
            'anonymous_likes' => $anonymousLikes,     // Guest users
            'anonymous_percent' => $anonymousPercent,

            // Time-based (real interactions only)
            'today_likes' => $todayLikes,
            'week_likes' => $weekLikes,
        ];
    }
}
