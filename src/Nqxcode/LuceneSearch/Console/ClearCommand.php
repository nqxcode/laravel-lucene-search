<?php namespace Nqxcode\LuceneSearch\Console;

use Illuminate\Console\Command;
use Config;
use Symfony\Component\Console\Output\NullOutput;

class ClearCommand extends Command
{
    protected $name = 'search:clear';
    protected $description = 'Clear the search index storage';

    public function fire()
    {
        if (!$this->option('verbose')) {
            $this->output = new NullOutput;
        }

        if (\File::isDirectory($indexPath = Config::get('laravel-lucene-search.index.path'))) {
            \File::deleteDirectory($indexPath);
            $this->info('Search index is cleared.');
        } else {
            $this->comment('There is nothing to clear..');
        }
    }
}
