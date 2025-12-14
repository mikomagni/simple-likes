<?php

namespace Mikomagni\SimpleLikes\Widgets;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mikomagni\SimpleLikes\Models\SimpleLike;
use Mikomagni\SimpleLikes\Traits\ResolvesUsers;
use Statamic\Widgets\Widget;

class TopUsersWidget extends Widget
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
        $limit = $config['top_users_limit'] ?? 5;

        // Cache widget data to reduce CP load
        $cacheTtl = config('simple-likes.cache.widget_top_users_ttl', 300);
        $cacheKey = "simple_likes_widget_top_users_{$limit}";
        $topUsers = Cache::remember($cacheKey, $cacheTtl, function () use ($limit) {
            return $this->getTopUsers($limit);
        });

        // Only show if there's data
        if ($topUsers->count() > 0) {
            return view('simple-likes::widgets.top-users', compact('topUsers'));
        }

        return '';
    }

    /**
     * Get top users (authenticated users only, by like count)
     */
    private function getTopUsers($limit = 5)
    {
        $topUserRows = SimpleLike::authenticated()
            ->select('user_id', DB::raw('COUNT(*) as likes_given'))
            ->groupBy('user_id')
            ->orderByDesc('likes_given')
            ->limit($limit)
            ->get();

        // Batch load all users at once
        $userIds = $topUserRows->pluck('user_id')->toArray();
        $users = collect($userIds)->mapWithKeys(fn ($id) => [$id => $this->findUser($id)])->filter();
        $canViewUsers = $this->canViewUsers();

        return $topUserRows->map(function ($item) use ($users, $canViewUsers) {
                $user = $users->get($item->user_id);

                return [
                    'user_id' => $item->user_id,
                    'name' => $user ? $this->getUserDisplayName($user) : "User #{$item->user_id}",
                    'likes_given' => $item->likes_given,
                    'avatar_url' => $user ? $this->getUserAvatarUrl($user) : null,
                    'avatar_initial' => $user ? strtoupper(substr($this->getUserDisplayName($user), 0, 1)) : 'U',
                    'can_view_users' => $canViewUsers,
                    'edit_url' => $user && $canViewUsers ? "/cp/users/{$item->user_id}/edit" : null,
                ];
            });
    }
}
