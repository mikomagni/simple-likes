<?php

namespace Mikomagni\SimpleLikes\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Statamic\Facades\Entry;
use Statamic\Facades\User;
use Mikomagni\SimpleLikes\Models\SimpleLike;
use Mikomagni\SimpleLikes\Traits\ResolvesUsers;

class SimpleLikesController extends Controller
{
    use ResolvesUsers;
    private function isValidEntryId(string $id): bool
    {
        if (empty($id) || strlen($id) > 255) {
            return false;
        }

        return (bool) preg_match('/^[a-zA-Z0-9_-]+$/', $id);
    }

    private function getUserIdentifier(Request $request): string
    {
        if (Auth::check()) {
            return (string) Auth::user()->id();
        }

        return 'guest_' . hash('sha256', $request->ip() . '|' . $request->userAgent());
    }

    public function toggle(Request $request, $id)
    {
        if (!$this->isValidEntryId($id)) {
            return response()->json(['error' => 'Invalid request. Please refresh the page and try again.'], 400);
        }

        $entry = Entry::find($id);
        if (!$entry) {
            return response()->json(['error' => 'Content not found. It may have been removed.'], 404);
        }

        $allowGuestLikes = $this->guestLikesAllowed($entry);
        $isLocked = (bool) $entry->get('simple_likes_locked', false);

        if ($isLocked) {
            return response()->json(['error' => 'Likes are currently disabled for this content.'], 403);
        }

        if (!Auth::check() && !$allowGuestLikes) {
            return response()->json(['error' => 'Please log in to like this content.'], 401);
        }

        $userId = $this->getUserIdentifier($request);
        $isGuest = !Auth::check();
        $ipHash = hash('sha256', $request->ip());

        return DB::transaction(function () use ($entry, $id, $userId, $isGuest, $ipHash) {
            $spamCheck = $this->checkForSpam($id, $userId, $ipHash);
            if ($spamCheck) {
                return response()->json(['error' => $spamCheck], 429);
            }

            $existingLike = SimpleLike::forEntry($id)->forUser($userId)->first();
            $hasLiked = $existingLike !== null;

            if ($hasLiked) {
                $existingLike->delete();
                $userHasLiked = false;
            } else {
                SimpleLike::create([
                    'entry_id' => $id,
                    'user_id' => $userId,
                    'user_type' => $isGuest ? 'guest' : 'authenticated',
                    'ip_hash' => $ipHash
                ]);
                $userHasLiked = true;
            }

            $realLikes = SimpleLike::getTotalLikesForEntry($id);
            $presetLikes = (int) $entry->get('simple_likes', 0);
            $totalLikes = $presetLikes + $realLikes;

            $this->clearLikesCache($id, $userId);

            return response()->json([
                'likes_count' => $totalLikes,
                'user_has_liked' => $userHasLiked
            ]);
        });
    }

    private function clearLikesCache($entryId, $userId)
    {
        Cache::forget("simple_likes_display_{$entryId}_{$userId}");
        Cache::forget("simple_likes_count_{$entryId}");
    }

    /**
     * GET /!/simple-likes/status?ids=abc,def,ghi
     */
    public function status(Request $request)
    {
        $idsParam = $request->get('ids', '');
        if (empty($idsParam)) {
            return response()->json(['error' => 'Missing ids parameter'], 400);
        }

        $ids = array_filter(array_map('trim', explode(',', $idsParam)));

        // Limit to prevent abuse
        if (count($ids) > 50) {
            return response()->json(['error' => 'Too many entries requested. Maximum is 50.'], 400);
        }

        $userId = $this->getUserIdentifier($request);

        // Filter to valid IDs only
        $validIds = array_filter($ids, fn($id) => $this->isValidEntryId($id));
        if (empty($validIds)) {
            return response()->json([]);
        }

        $entries = Entry::query()->whereIn('id', $validIds)->get()->keyBy->id();

        $likeCounts = SimpleLike::select('entry_id', DB::raw('COUNT(*) as count'))
            ->whereIn('entry_id', $validIds)
            ->groupBy('entry_id')
            ->pluck('count', 'entry_id');

        $userLikedIds = SimpleLike::whereIn('entry_id', $validIds)
            ->where('user_id', $userId)
            ->pluck('entry_id')
            ->flip();

        $results = [];
        foreach ($validIds as $id) {
            $entry = $entries->get($id);
            if (!$entry) {
                continue;
            }

            $presetLikes = (int) $entry->get('simple_likes', 0);
            $realLikes = $likeCounts->get($id, 0);

            $results[$id] = [
                'count' => $presetLikes + $realLikes,
                'liked' => isset($userLikedIds[$id]),
            ];
        }

        return response()->json($results);
    }

    /**
     * GET /!/simple-likes/global-stats
     */
    public function globalStats(Request $request)
    {
        $cacheTtl = $this->getCacheTtl('global');

        $stats = Cache::remember('simple_likes_global_stats_api', $cacheTtl, function () {
            return SimpleLike::getGlobalStats(includeTimeRanges: true);
        });

        return response()->json($stats);
    }

    /**
     * GET /!/simple-likes/popular?limit=5&collection=news
     */
    public function popular(Request $request)
    {
        $limit = (int) $request->get('limit', 5);
        $collection = $request->get('collection');
        $cacheTtl = $this->getCacheTtl('popular');
        $cacheKey = 'simple_likes_popular_api_' . $limit . '_' . ($collection ?? 'all');

        $data = Cache::remember($cacheKey, $cacheTtl, function () use ($limit, $collection) {
            $entriesWithUserLikes = SimpleLike::select('entry_id', DB::raw('COUNT(*) as actual_likes'))
                ->groupBy('entry_id')
                ->get()
                ->keyBy('entry_id');

            $entryIdsWithLikes = $entriesWithUserLikes->keys()->toArray();

            $query = Entry::query();
            if ($collection) {
                $query->where('collection', $collection);
            }

            $entriesWithUserLikesQuery = (clone $query)->whereIn('id', $entryIdsWithLikes);

            $entriesWithStartingCount = (clone $query)
                ->where('simple_likes', '>', 0)
                ->whereNotIn('id', $entryIdsWithLikes)
                ->get();

            $entries = $entriesWithUserLikesQuery->get()->merge($entriesWithStartingCount);

            return $entries->map(function ($entry) use ($entriesWithUserLikes) {
                $startingCount = (int) $entry->get('simple_likes', 0);
                $actualLikes = isset($entriesWithUserLikes[$entry->id()]) ? $entriesWithUserLikes[$entry->id()]->actual_likes : 0;

                return [
                    'entry_id' => $entry->id(),
                    'title' => $entry->get('title'),
                    'url' => $entry->url(),
                    'collection' => $entry->collectionHandle(),
                    'likes_count' => $startingCount + $actualLikes,
                ];
            })
            ->sortByDesc('likes_count')
            ->take($limit)
            ->values()
            ->toArray();
        });

        return response()->json($data);
    }

    /**
     * GET /!/simple-likes/activity?limit=10&hours=24
     */
    public function activity(Request $request)
    {
        $limit = (int) $request->get('limit', 10);
        $hours = (int) $request->get('hours', 24);
        $cacheTtl = $this->getCacheTtl('activity');
        $cacheKey = 'simple_likes_activity_api_' . $limit . '_' . $hours;

        $data = Cache::remember($cacheKey, $cacheTtl, function () use ($limit, $hours) {
            $likes = SimpleLike::where('created_at', '>=', now()->subHours($hours))
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();

            $entryIds = $likes->pluck('entry_id')->unique()->toArray();
            $entries = Entry::query()->whereIn('id', $entryIds)->get()->keyBy->id();

            return $likes->map(function ($like) use ($entries) {
                    $entry = $entries->get($like->entry_id);
                    if (!$entry) {
                        return null;
                    }

                    return [
                        'entry_id' => $like->entry_id,
                        'entry_title' => $entry->get('title'),
                        'entry_url' => $entry->url(),
                        'user_type' => $like->user_type,
                        'time_ago' => $like->created_at->diffForHumans(),
                        'created_at' => $like->created_at->toIso8601String(),
                    ];
                })
                ->filter()
                ->values()
                ->toArray();
        });

        return response()->json($data);
    }

    /**
     * GET /!/simple-likes/weekly?days=7
     */
    public function weekly(Request $request)
    {
        $days = (int) $request->get('days', 7);
        $cacheTtl = $this->getCacheTtl('weekly');
        $cacheKey = 'simple_likes_weekly_api_' . $days;

        $data = Cache::remember($cacheKey, $cacheTtl, function () use ($days) {
            $startDate = now()->subDays($days - 1)->startOfDay();
            $dailyCounts = SimpleLike::query()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', $startDate)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->pluck('count', 'date');

            $results = collect();
            $maxCount = 0;

            for ($i = $days - 1; $i >= 0; $i--) {
                $date = now()->subDays($i);
                $dateKey = $date->toDateString();
                $count = (int) ($dailyCounts[$dateKey] ?? 0);

                if ($count > $maxCount) {
                    $maxCount = $count;
                }

                $results->push([
                    'date' => $date->format('M j'),
                    'full_date' => $dateKey,
                    'day_name' => $date->format('l'),
                    'likes_count' => $count,
                ]);
            }

            return $results->map(function ($item) use ($maxCount) {
                $item['percentage'] = $maxCount > 0 ? round(($item['likes_count'] / $maxCount) * 100) : 0;
                $item['max_count'] = $maxCount;
                return $item;
            })->toArray();
        });

        return response()->json($data);
    }

    /**
     * GET /!/simple-likes/top-users?limit=5
     */
    public function topUsers(Request $request)
    {
        $limit = (int) $request->get('limit', 10);
        $cacheTtl = $this->getCacheTtl('top_users');
        $cacheKey = 'simple_likes_top_users_api_' . $limit;

        $data = Cache::remember($cacheKey, $cacheTtl, function () use ($limit) {
            $topUserRows = SimpleLike::select('user_id', DB::raw('COUNT(*) as likes_count'))
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
                        'name' => $user->get('name') ?: 'Anonymous',
                        'avatar' => $this->getUserAvatarUrl($user),
                        'initials' => $user->initials(),
                        'likes_count' => $row->likes_count,
                    ];
                })
                ->filter()
                ->values()
                ->toArray();
        });

        return response()->json($data);
    }

    /**
     * GET /!/simple-likes/stats-all
     */
    public function statsAll(Request $request)
    {
        $popularLimit = (int) $request->get('popular_limit', 5);
        $activityLimit = (int) $request->get('activity_limit', 8);
        $activityHours = (int) $request->get('activity_hours', 168);
        $weeklyDays = (int) $request->get('weekly_days', 7);
        $topUsersLimit = (int) $request->get('top_users_limit', 5);
        $collections = $request->get('collections') ? explode(',', $request->get('collections')) : [];

        $normalizedCollections = array_map('trim', $collections);
        sort($normalizedCollections);

        $cacheTtl = $this->getCacheTtl('stats_all');
        $cacheKey = 'simple_likes_stats_all_' . md5(json_encode([
            'popular_limit' => $popularLimit,
            'activity_limit' => $activityLimit,
            'activity_hours' => $activityHours,
            'weekly_days' => $weeklyDays,
            'top_users_limit' => $topUsersLimit,
            'collections' => implode(',', $normalizedCollections),
        ]));

        $data = Cache::remember($cacheKey, $cacheTtl, function () use (
            $popularLimit, $activityLimit, $activityHours, $weeklyDays, $topUsersLimit, $collections
        ) {
            $entriesWithUserLikes = SimpleLike::select('entry_id', DB::raw('COUNT(*) as actual_likes'))
                ->groupBy('entry_id')
                ->get()
                ->keyBy('entry_id');

            $allLikedEntryIds = $entriesWithUserLikes->keys()->toArray();

            $globalStats = SimpleLike::getGlobalStats(
                includeTimeRanges: true,
                precomputedEntryIds: $allLikedEntryIds
            );

            $collectionCounts = [];
            if (!empty($collections)) {
                foreach ($collections as $collection) {
                    $collection = trim($collection);
                    $collectionEntries = Entry::query()
                        ->where('collection', $collection)
                        ->get();

                    $startingCount = $collectionEntries->sum(fn ($entry) => (int) $entry->get('simple_likes', 0));

                    $actualLikes = 0;
                    foreach ($collectionEntries as $entry) {
                        $actualLikes += $entriesWithUserLikes->get($entry->id())?->actual_likes ?? 0;
                    }

                    $collectionCounts[$collection] = $startingCount + $actualLikes;
                }
            }

            $entries = Entry::query()->whereIn('id', $allLikedEntryIds)->get()->keyBy->id();

            $entriesWithStartingCount = Entry::query()
                ->where('simple_likes', '>', 0)
                ->whereNotIn('id', $allLikedEntryIds)
                ->get();

            $popular = $entries->merge($entriesWithStartingCount->keyBy->id())
                ->map(function ($entry) use ($entriesWithUserLikes) {
                    $startingCount = (int) $entry->get('simple_likes', 0);
                    $actualLikes = $entriesWithUserLikes->get($entry->id())?->actual_likes ?? 0;
                    return [
                        'entry' => $entry,
                        'likes_count' => $startingCount + $actualLikes,
                    ];
                })
                ->filter(fn($item) => $item['likes_count'] > 0)
                ->sortByDesc('likes_count')
                ->take($popularLimit)
                ->map(fn($item) => [
                    'entry_id' => $item['entry']->id(),
                    'title' => $item['entry']->get('title'),
                    'url' => $item['entry']->url(),
                    'collection' => $item['entry']->collectionHandle(),
                    'likes_count' => $item['likes_count'],
                ])
                ->values()
                ->toArray();

            $recentLikes = SimpleLike::where('created_at', '>=', now()->subHours($activityHours))
                ->orderByDesc('created_at')
                ->limit($activityLimit)
                ->get();

            $activityEntryIds = $recentLikes->pluck('entry_id')->unique()->toArray();
            $activityEntries = Entry::query()->whereIn('id', $activityEntryIds)->get()->keyBy->id();

            $activity = $recentLikes->map(function ($like) use ($activityEntries) {
                    $entry = $activityEntries->get($like->entry_id);
                    return [
                        'entry_id' => $like->entry_id,
                        'entry_title' => $entry?->get('title') ?? 'Unknown',
                        'entry_url' => $entry?->url(),
                        'user_type' => $like->user_type,
                        'time_ago' => $like->created_at->diffForHumans(),
                        'created_at' => $like->created_at->toIso8601String(),
                    ];
                })
                ->toArray();

            $startDate = now()->subDays($weeklyDays - 1)->startOfDay();
            $dailyCounts = SimpleLike::query()
                ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
                ->where('created_at', '>=', $startDate)
                ->groupBy(DB::raw('DATE(created_at)'))
                ->pluck('count', 'date');

            $weekly = collect(range(0, $weeklyDays - 1))
                ->map(function ($daysAgo) use ($dailyCounts) {
                    $date = now()->subDays($daysAgo);
                    $dateKey = $date->format('Y-m-d');
                    return [
                        'date' => $date->format('D'),
                        'full_date' => $dateKey,
                        'day_name' => $date->format('l'),
                        'likes_count' => (int) ($dailyCounts[$dateKey] ?? 0),
                    ];
                })
                ->reverse()
                ->values();

            $maxCount = $weekly->max('likes_count') ?: 1;
            $weekly = $weekly->map(function ($day) use ($maxCount) {
                $day['percentage'] = round(($day['likes_count'] / $maxCount) * 100);
                $day['max_count'] = $maxCount;
                return $day;
            })->toArray();

            $topUserRows = SimpleLike::select('user_id', DB::raw('COUNT(*) as likes_count'))
                ->where('user_type', 'authenticated')
                ->groupBy('user_id')
                ->orderByDesc('likes_count')
                ->limit($topUsersLimit)
                ->get();

            $topUserIds = $topUserRows->pluck('user_id')->toArray();
            $topUsersMap = User::query()->whereIn('id', $topUserIds)->get()->keyBy->id();

            $topUsers = $topUserRows->map(function ($row) use ($topUsersMap) {
                    $user = $topUsersMap->get($row->user_id);
                    if (!$user) {
                        return null;
                    }

                    return [
                        'user_id' => $row->user_id,
                        'name' => $user->get('name') ?: 'Anonymous',
                        'avatar' => $this->getUserAvatarUrl($user),
                        'initials' => $user->initials(),
                        'likes_count' => $row->likes_count,
                    ];
                })
                ->filter()
                ->values()
                ->toArray();

            return [
                'global' => $globalStats,
                'collections' => $collectionCounts,
                'popular' => $popular,
                'activity' => $activity,
                'weekly' => $weekly,
                'top_users' => $topUsers,
            ];
        });

        return response()->json($data);
    }

    private function getCacheTtl(string $type): int
    {
        $config = config('simple-likes.cache', []);
        $defaultTtl = $config['default_ttl'] ?? config('simple-likes.cache_ttl', 1800);

        return match ($type) {
            'entry' => $config['entry_ttl'] ?? 300,
            'global' => $config['global_ttl'] ?? $defaultTtl,
            'activity' => $config['activity_ttl'] ?? 60,
            'popular' => $config['popular_ttl'] ?? $defaultTtl,
            'weekly' => $config['weekly_ttl'] ?? $defaultTtl,
            'top_users' => $config['top_users_ttl'] ?? $defaultTtl,
            'stats_all' => $config['stats_all_ttl'] ?? 60,
            default => $defaultTtl,
        };
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

    private function checkForSpam(string $entryId, string $userId, string $ipHash): ?string
    {
        $rapidFireMax = config('simple-likes.rate_limiting.rapid_fire.max_likes', 5);
        $rapidFireWindow = config('simple-likes.rate_limiting.rapid_fire.time_window', 10);
        $userMaxPerMinute = config('simple-likes.rate_limiting.user_limits.max_likes_per_minute', 10);
        $maxTogglesPerEntry = config('simple-likes.rate_limiting.user_limits.max_toggles_per_entry', 3);
        $toggleWindow = config('simple-likes.rate_limiting.user_limits.toggle_time_window', 60);

        $checks = SimpleLike::query()
            ->selectRaw("
                SUM(CASE WHEN ip_hash = ? AND created_at >= ? THEN 1 ELSE 0 END) as ip_recent,
                SUM(CASE WHEN user_id = ? AND created_at >= ? THEN 1 ELSE 0 END) as user_recent,
                SUM(CASE WHEN entry_id = ? AND user_id = ? AND created_at >= ? THEN 1 ELSE 0 END) as entry_toggles
            ", [
                $ipHash, now()->subSeconds($rapidFireWindow),
                $userId, now()->subMinute(),
                $entryId, $userId, now()->subSeconds($toggleWindow),
            ])
            ->lockForUpdate()
            ->first();

        if ((int) $checks->ip_recent >= $rapidFireMax) {
            return __('simple-likes::messages.rate_limited');
        }

        if ((int) $checks->user_recent >= $userMaxPerMinute) {
            return __('simple-likes::messages.rate_limited');
        }

        if ((int) $checks->entry_toggles >= $maxTogglesPerEntry) {
            return __('simple-likes::messages.rate_limited');
        }

        return null;
    }
}
