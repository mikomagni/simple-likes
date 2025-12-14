<?php

namespace Mikomagni\SimpleLikes\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Mikomagni\SimpleLikes\Models\SimpleLike;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\Entry;

class WarmLikesCache extends Command
{
    use RunsInPlease;

    protected $signature = 'statamic:simple-likes:warm-cache
                            {--limit=50 : Number of popular entries to warm}
                            {--all : Warm cache for all entries with likes}
                            {--stats : Also warm statistics cache}';

    protected $description = 'Warm the Simple Likes cache for faster page loads';

    public function handle(): int
    {
        $this->info('Starting Simple Likes cache warming...');

        $limit = (int) $this->option('limit');
        $warmAll = $this->option('all');
        $warmStats = $this->option('stats');

        if ($warmAll) {
            $entryIds = $this->getAllEntriesWithLikes();
            $this->info("Warming cache for all {$entryIds->count()} entries with likes...");
        } else {
            $entryIds = $this->getPopularEntryIds($limit);
            $this->info("Warming cache for top {$entryIds->count()} popular entries...");
        }

        if ($entryIds->isEmpty()) {
            $this->warn('No entries found to warm.');
            return self::SUCCESS;
        }

        $bar = $this->output->createProgressBar($entryIds->count());
        $bar->start();

        $warmed = 0;
        foreach ($entryIds as $entryId) {
            $this->warmEntryCache($entryId);
            $warmed++;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        if ($warmStats) {
            $this->warmStatisticsCache();
        }

        $this->info("Successfully warmed cache for {$warmed} entries.");

        return self::SUCCESS;
    }

    private function getPopularEntryIds(int $limit): \Illuminate\Support\Collection
    {
        return SimpleLike::select('entry_id')
            ->groupBy('entry_id')
            ->orderByRaw('COUNT(*) DESC')
            ->limit($limit)
            ->pluck('entry_id');
    }

    private function getAllEntriesWithLikes(): \Illuminate\Support\Collection
    {
        return SimpleLike::distinct('entry_id')->pluck('entry_id');
    }

    private function warmEntryCache(string $entryId): void
    {
        $cacheKey = "simple_likes_count_{$entryId}";
        Cache::remember($cacheKey, 3600, function () use ($entryId) {
            $entry = Entry::find($entryId);
            return $entry ? (int) $entry->get('simple_likes', 0) : 0;
        });
    }

    private function warmStatisticsCache(): void
    {
        $this->info('Warming global statistics cache...');

        $stats = SimpleLike::getGlobalStats(includeTimeRanges: true);

        $cacheTtl = config('simple-likes.cache.global_ttl', 1800);
        Cache::put('simple_likes_global_stats_api', $stats, $cacheTtl);

        $this->info('Global statistics cache warmed.');
    }
}
