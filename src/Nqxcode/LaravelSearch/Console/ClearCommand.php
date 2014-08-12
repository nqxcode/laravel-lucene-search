<?php  namespace Nqxcode\LaravelSearch\Console;

use Illuminate\Console\Command;
use Config;

class ClearCommand extends Command
{
    protected $name = 'search:clear';
    protected $description = 'Clear the search index storage';

    public function fire()
    {
        if ($result = rmdir_recursive(Config::get('laravel-lucene-search::index.path'))) {
            $this->info('Search index storage cleared!');
        } else {
            $this->info('No search index storage found..');
        }
    }
}
