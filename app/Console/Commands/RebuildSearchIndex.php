<?php

namespace App\Console\Commands;

use App\Services\DocSearchService;
use Illuminate\Console\Command;

class RebuildSearchIndex extends Command
{
    protected $signature   = 'doc:rebuild-search-index';
    protected $description = 'Rebuild the FTS5 full-text search index for documents.';

    public function handle(): int
    {
        if (! DocSearchService::ftsAvailable()) {
            $this->warn('FTS5 index not available (non-SQLite database or table missing). Run migrations first.');
            return self::FAILURE;
        }

        $this->info('Rebuilding FTS5 search index…');
        $count = DocSearchService::rebuild();
        $this->info("Done. Indexed {$count} document(s).");
        return self::SUCCESS;
    }
}
