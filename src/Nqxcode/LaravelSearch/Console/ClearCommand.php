<?php  namespace Nqxcode\LaravelSearch\Console;

use Illuminate\Console\Command;

class ClearCommand extends Command
{
    protected $name = 'search:clear';
    protected $description = 'Clear the search index';

    public function fire()
    {
        $indexPath = \Config::get('laravel-search::index_path');

        if ($result = rmdir_recursive($indexPath)) {
            $this->info('Search index storage cleared!');
        } else {
            $this->error('No Search index storage found..');
        }
    }
}
