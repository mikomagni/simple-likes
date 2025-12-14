<?php

namespace Mikomagni\SimpleLikes\Widgets;

use Illuminate\Support\Facades\Cache;
use Mikomagni\SimpleLikes\Models\SimpleLike;
use Mikomagni\SimpleLikes\Traits\ResolvesUsers;
use Statamic\Facades\Entry;
use Statamic\Widgets\Widget;

class RecentActivityWidget extends Widget
{
    use ResolvesUsers;

    /**
     * The HTML that should be shown in the widget
     *
     * @return string|\Illuminate\View\View
     */
    public function html()
    {
        $config = config('simple-likes.widget', []);
        $limit = $config['recent_activity_limit'] ?? 5;

        // Cache widget data (shorter TTL for activity feed)
        $cacheTtl = config('simple-likes.cache.widget_activity_ttl', 120);
        $cacheKey = "simple_likes_widget_activity_{$limit}";
        $recentActivity = Cache::remember($cacheKey, $cacheTtl, function () use ($limit) {
            return $this->getRecentActivity($limit);
        });

        // Only show if there's data
        if ($recentActivity->count() > 0) {
            $overview = $this->getOverviewData();
            return view('simple-likes::widgets.recent-activity', compact('recentActivity', 'overview'));
        }

        return '';
    }

    /**
     * Get recent activity (last 24 hours)
     */
    private function getRecentActivity($limit = 5)
    {
        $likes = SimpleLike::where('created_at', '>=', now()->subDay())
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();

        // Batch load all entries at once
        $entryIds = $likes->pluck('entry_id')->unique()->toArray();
        $entries = Entry::query()->whereIn('id', $entryIds)->get()->keyBy->id();

        // Batch load all users at once
        $userIds = $likes->where('user_type', 'authenticated')->pluck('user_id')->unique()->toArray();
        $users = collect($userIds)->mapWithKeys(fn ($id) => [$id => $this->findUser($id)])->filter();

        return $likes->map(function ($like) use ($entries, $users) {
                $entry = $entries->get($like->entry_id);
                $user = null;
                $userName = 'Guest';

                // Get user info for authenticated users
                if ($like->user_type === 'authenticated') {
                    $user = $users->get($like->user_id);
                    $userName = $user ? $this->getUserDisplayName($user) : "User #{$like->user_id}";
                }

                return [
                    'entry_title' => $entry ? $entry->get('title') : 'Unknown Entry',
                    'entry_cp_url' => $entry ? "/cp/collections/{$entry->collectionHandle()}/entries/{$entry->id()}" : null,
                    'user_type' => $like->user_type,
                    'user_name' => $userName,
                    'user_edit_url' => $user && $like->user_type === 'authenticated' ? $this->getUserEditUrl($like->user_id) : null,
                    'created_at' => $like->created_at,
                    'avatar_url' => $user ? $this->getUserAvatarUrl($user) : null,
                    'avatar_initial' => $user ? strtoupper(substr($this->getUserDisplayName($user), 0, 1)) : 'G',
                ];
            })
            ->filter(fn($item) => $item['entry_title'] !== 'Unknown Entry');
    }

    /**
     * Get overview data for last 24 hours count
     */
    private function getOverviewData()
    {
        return [
            'recent_activity' => SimpleLike::where('created_at', '>=', now()->subDay())->count(),
        ];
    }
}
