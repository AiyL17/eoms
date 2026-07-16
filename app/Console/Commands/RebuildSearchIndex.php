<?php

namespace App\Console\Commands;

use App\Services\EoSearchService;
use Illuminate\Console\Command;

class RebuildSearchIndex extends Command
{
    protected $signature   = 'eo:rebuild-search-index';
    protected $description = 'Rebuild the FTS5 full-text search index for executive orders.';

    public function handle(): int
    {
        if (! EoSearchService::ftsAvailable()) {
            $this->warn('FTS5 index not available (non-SQLite database or table missing). Run migrations first.');
            return self::FAILURE;
        }

        $this->info('Rebuilding FTS5 search index…');
        $count = EoSearchService::rebuild();
        $this->info("Done. Indexed {$count} executive order(s).");
        return self::SUCCESS;
    }
}
