<?php

namespace Mikomagni\SimpleLikes\Console\Commands;

use Illuminate\Console\Command;
use Mikomagni\SimpleLikes\Models\SimpleLike;

class PruneGuestLikes extends Command
{
    protected $signature = 'simple-likes:prune-guests
                            {--days=30 : Delete guest likes older than this many days}
                            {--dry-run : Show what would be deleted without actually deleting}';

    protected $description = 'Remove old guest likes to prevent database bloat';

    public function handle()
    {
        $days = (int) $this->option('days');
        $dryRun = $this->option('dry-run');

        $cutoffDate = now()->subDays($days);

        $query = SimpleLike::where('user_type', 'guest')
            ->where('created_at', '<', $cutoffDate);

        $count = $query->count();

        if ($count === 0) {
            $this->info('No guest likes older than ' . $days . ' days found.');
            return 0;
        }

        if ($dryRun) {
            $this->info("[Dry run] Would delete {$count} guest likes older than {$days} days.");
            return 0;
        }

        $deleted = $query->delete();

        $this->info("Deleted {$deleted} guest likes older than {$days} days.");

        return 0;
    }
}
