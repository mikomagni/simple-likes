<?php

namespace Mikomagni\SimpleLikes\Tags;

use Statamic\Tags\Tags;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Mikomagni\SimpleLikes\Models\SimpleLike as SimpleLikeModel;
use Mikomagni\SimpleLikes\Traits\ResolvesUsers;
use Statamic\Facades\Entry;
use Statamic\Facades\User;

class SimpleLike extends Tags
{
    use ResolvesUsers;

    protected static $handle = 'simple_like';

    public function index()
    {
        $entry = $this->context->get('entry');
        if (!$entry) {
            $entryId = $this->context->get('id');
            if ($entryId instanceof \Statamic\Fields\Value) {
                $entryId = $entryId->value();
            }
            $entry = $entryId ? Entry::find($entryId) : null;
        }

        if (!$entry) {
            return '<p style="color: red;">No entry found</p>';
        }

        $allowGuestLikes = $this->guestLikesAllowed($entry);
        $isLocked = (bool) $entry->get('simple_likes_locked', false);

        $userHasLiked = false;
        $isAuthenticated = Auth::check();
        $canInteract = !$isLocked && ($isAuthenticated || $allowGuestLikes);

        $userId = $isAuthenticated ? Auth::user()->id() : 'guest_' . hash('sha256', request()->ip() . '|' . request()->userAgent());

        $presetLikes = (int) $entry->get('simple_likes', 0);
        $realLikes = SimpleLikeModel::getTotalLikesForEntry($entry->id());
        $likesCount = $presetLikes + $realLikes;

        $cacheKey = "simple_likes_display_{$entry->id()}_{$userId}";
        $displayData = Cache::remember($cacheKey, 1800, function() use ($entry, $userId, $canInteract) {
            if ($canInteract) {
                $userHasLiked = SimpleLikeModel::getUserHasLiked($entry->id(), $userId);
            } else {
                $userHasLiked = false;
            }

            return [
                'user_has_liked' => $userHasLiked,
                'cached_at' => now()->timestamp
            ];
        });

        $userHasLiked = $displayData['user_has_liked'];

        $customTemplate = $this->params->get('template_from')
            ?? config('simple-likes.default_template');

        $templateData = [
            'entry_id' => $entry->id(),
            'likes_count' => $likesCount,
            'user_has_liked' => $userHasLiked,
            'is_authenticated' => $isAuthenticated,
            'can_interact' => $canInteract,
            'allow_guest_likes' => $allowGuestLikes,
            'is_locked' => $isLocked,
        ];

        if ($customTemplate) {
            return \Statamic\View\View::make($customTemplate, $templateData)->render();
        }

        return view('simple-likes::like-button', $templateData)->render();
    }

    /** {{ simple_like:stats }} */
    public function stats()
    {
        $cacheTtl = $this->params->get('cache', config('simple-likes.cache_ttl', 1800));

        $callback = function () {
            return SimpleLikeModel::getGlobalStats(includeTimeRanges: true);
        };

        if ($cacheTtl > 0) {
            return Cache::remember('simple_likes_stats', $cacheTtl, $callback);
        }

        return $callback();
    }

    /** {{ simple_like:popular limit="5" }} */
    public function popular()
    {
        $limit = $this->params->get('limit', 5);
        $collection = $this->params->get('collection');
        $cacheTtl = $this->params->get('cache', config('simple-likes.cache_ttl', 1800));
        $cacheKey = 'simple_likes_popular_' . $limit . '_' . ($collection ?? 'all');

        $callback = function () use ($limit, $collection) {
            $entriesWithUserLikes = SimpleLikeModel::select('entry_id', DB::raw('COUNT(*) as actual_likes'))
                ->groupBy('entry_id')
                ->get()
                ->keyBy('entry_id');

            $entryIdsWithLikes = $entriesWithUserLikes->keys()->toArray();

            $query = Entry::query();
            if ($collection) {
                $query->where('collection', $collection);
            }

            $entries = $query->get()->filter(function ($entry) use ($entriesWithUserLikes) {
                $hasBaseLikes = (int) $entry->get('simple_likes', 0) > 0;
                $hasUserLikes = isset($entriesWithUserLikes[$entry->id()]);
                return $hasBaseLikes || $hasUserLikes;
            });

            return $entries->map(function ($entry) use ($entriesWithUserLikes) {
                $startingCount = (int) $entry->get('simple_likes', 0);
                $actualLikes = isset($entriesWithUserLikes[$entry->id()]) ? $entriesWithUserLikes[$entry->id()]->actual_likes : 0;

                return [
                    'entry_id' => $entry->id(),
                    'title' => $entry->get('title'),
                    'url' => $entry->url(),
                    'collection' => $entry->collectionHandle(),
                    'likes_count' => $startingCount + $actualLikes,
                    'entry' => $entry,
                ];
            })
            ->sortByDesc('likes_count')
            ->take($limit)
            ->values();
        };

        if ($cacheTtl > 0) {
            return Cache::remember($cacheKey, $cacheTtl, $callback);
        }

        return $callback();
    }

    /** {{ simple_like:activity limit="10" hours="24" }} */
    public function activity()
    {
        $limit = $this->params->get('limit', 10);
        $hours = $this->params->get('hours', 24);
        $cacheTtl = $this->params->get('cache', config('simple-likes.cache_ttl', 1800));
        $cacheKey = 'simple_likes_activity_' . $limit . '_' . $hours;

        $callback = function () use ($limit, $hours) {
            $likes = SimpleLikeModel::where('created_at', '>=', now()->subHours($hours))
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            $entryIds = $likes->pluck('entry_id')->unique()->toArray();
            $entries = Entry::query()->whereIn('id', $entryIds)->get()->keyBy->id();

            return $likes->map(function ($like) use ($entries) {
                    $entry = $entries->get($like->entry_id);

                    return [
                        'entry_title' => $entry ? $entry->get('title') : 'Unknown Entry',
                        'entry_url' => $entry ? $entry->url() : null,
                        'entry_id' => $like->entry_id,
                        'user_type' => $like->user_type,
                        'created_at' => $like->created_at,
                        'time_ago' => $like->created_at->diffForHumans(),
                        'entry' => $entry,
                    ];
                })
                ->filter(fn($item) => $item['entry_title'] !== 'Unknown Entry');
        };

        if ($cacheTtl > 0) {
            return Cache::remember($cacheKey, $cacheTtl, $callback);
        }

        return $callback();
    }

    /** {{ simple_like:weekly days="7" }} */
    public function weekly()
    {
        $days = $this->params->get('days', 7);
        $cacheTtl = $this->params->get('cache', config('simple-likes.cache_ttl', 1800));
        $cacheKey = 'simple_likes_weekly_' . $days;

        $callback = function () use ($days) {
            $startDate = now()->subDays($days - 1)->startOfDay();
            $dailyCounts = SimpleLikeModel::query()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', $startDate)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->pluck('count', 'date');

            $data = collect();
            $maxCount = 0;

            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dateKey = $date->toDateString();
                $count = (int) ($dailyCounts[$dateKey] ?? 0);

                if ($count > $maxCount) {
                    $maxCount = $count;
                }

                $data->push([
                    'date' => $date->format('M j'),
                    'full_date' => $dateKey,
                    'day_name' => $date->format('l'),
                    'likes_count' => $count,
                ]);
            }

            return $data->map(function ($item) use ($maxCount) {
                $item['percentage'] = $maxCount > 0 ? ($item['likes_count'] / $maxCount) * 100 : 0;
                $item['max_count'] = $maxCount;
                return $item;
            });
        };

        if ($cacheTtl > 0) {
            return Cache::remember($cacheKey, $cacheTtl, $callback);
        }

        return $callback();
    }

    /** {{ simple_like:top_users limit="10" }} */
    public function topUsers()
    {
        $limit = $this->params->get('limit', 10);
        $cacheTtl = $this->params->get('cache', config('simple-likes.cache_ttl', 1800));
        $cacheKey = 'simple_likes_top_users_' . $limit;

        $callback = function () use ($limit) {
            $topUserRows = SimpleLikeModel::select('user_id', DB::raw('COUNT(*) as likes_count'))
                ->where('user_type', 'authenticated')
                ->groupBy('user_id')
                ->orderByDesc('likes_count')
                ->limit($limit)
                ->get();

            $userIds = $topUserRows->pluck('user_id')->toArray();
            $users = User::query()->whereIn('id', $userIds)->get()->keyBy->id();

            return $topUserRows->map(function ($row) use ($users) {
                    $user = $users->get($row->user_id);

                    if (!$user) {
                        return null;
                    }

                    return [
                        'user_id' => $row->user_id,
                        'likes_count' => $row->likes_count,
                        'name' => $user->get('name') ?: 'Anonymous',
                        'avatar' => $this->getUserAvatarUrl($user),
                        'initials' => $user->initials(),
                        'user' => $user,
                    ];
                })
                ->filter()
                ->values();
        };

        if ($cacheTtl > 0) {
            return Cache::remember($cacheKey, $cacheTtl, $callback);
        }

        return $callback();
    }

    /** {{ simple_like:my_likes collection="news" limit="10" }} */
    public function myLikes()
    {
        if (!Auth::check()) {
            return [];
        }

        $userId = Auth::user()->id();
        $collection = $this->params->get('collection');
        $limit = $this->params->get('limit', 10);

        $likes = SimpleLikeModel::forUser($userId)
            ->orderByDesc('created_at')
            ->get();

        $entryIds = $likes->pluck('entry_id')->unique()->toArray();
        $allEntries = Entry::query()->whereIn('id', $entryIds)->get()->keyBy->id();

        $entries = $likes->map(function ($like) use ($collection, $allEntries) {
            $entry = $allEntries->get($like->entry_id);

            if (!$entry) {
                return null;
            }

            if ($collection && $entry->collectionHandle() !== $collection) {
                return null;
            }

            return [
                'entry_id' => $entry->id(),
                'title' => $entry->get('title'),
                'url' => $entry->url(),
                'collection' => $entry->collectionHandle(),
                'liked_at' => $like->created_at,
                'liked_ago' => $like->created_at->diffForHumans(),
                'entry' => $entry,
            ];
        })
        ->filter()
        ->take($limit)
        ->values();

        return $entries;
    }

    /** {{ simple_like:my_likes_count collection="news" period="today|week|month" }} */
    public function myLikesCount()
    {
        if (!Auth::check()) {
            return 0;
        }

        $userId = Auth::user()->id();
        $collection = $this->params->get('collection');
        $period = $this->params->get('period');

        $query = SimpleLikeModel::forUser($userId);

        if ($period === 'today') {
            $query->whereDate('created_at', today());
        } elseif ($period === 'week') {
            $query->where('created_at', '>=', now()->subWeek());
        } elseif ($period === 'month') {
            $query->where('created_at', '>=', now()->subMonth());
        }

        if ($collection) {
            $entries = Entry::query()->where('collection', $collection)->get();
            $entryIds = $entries->map(fn($entry) => $entry->id())->toArray();

            return $query->whereIn('entry_id', $entryIds)->count();
        }

        return $query->count();
    }

    /** {{ simple_like:count id="xxx" collection="all" }} */
    public function count()
    {
        $entryId = $this->params->get('id');
        $collection = $this->params->get('collection');

        if ($collection && $collection !== 'all') {
            $entries = Entry::query()->where('collection', $collection)->get();
            $entryIds = $entries->map(fn($entry) => $entry->id())->toArray();

            $totalStartingCount = $entries->sum(function ($entry) {
                return (int) $entry->get('simple_likes', 0);
            });
            $totalActualLikes = SimpleLikeModel::whereIn('entry_id', $entryIds)->count();
            return $totalStartingCount + $totalActualLikes;
        }

        if ($entryId) {
            $entry = Entry::find($entryId);
            $startingCount = (int) ($entry ? $entry->get('simple_likes', 0) : 0);
            $actualLikes = SimpleLikeModel::where('entry_id', $entryId)->count();
            return $startingCount + $actualLikes;
        }

        $cacheTtl = config('simple-likes.cache_ttl', 1800);
        return Cache::remember('simple_likes_total_count', $cacheTtl, function () {
            $entryIdsWithLikes = SimpleLikeModel::distinct('entry_id')->pluck('entry_id');

            $entries = Entry::query()->whereIn('id', $entryIdsWithLikes->toArray())->get();
            $totalStartingCount = $entries->sum(fn ($entry) => (int) $entry->get('simple_likes', 0));

            $totalActualLikes = SimpleLikeModel::count();
            return $totalStartingCount + $totalActualLikes;
        });
    }

    private function guestLikesAllowed($entry)
    {
        $fieldDefault = false;
        $field = $entry->blueprint()->field('simple_likes');
        if ($field) {
            $fieldConfig = $field->config();
            $fieldDefault = $fieldConfig['allow_guest_likes'] ?? false;
        }

        $guestField = $entry->blueprint()->field('simple_likes_guest_allowed');
        if ($guestField) {
            return (bool) $entry->get('simple_likes_guest_allowed', false);
        }

        return $fieldDefault;
    }
}
