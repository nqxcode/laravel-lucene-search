<?php  namespace Nqxcode\LaravelSearch\Console;

use Illuminate\Console\Command;
use Nqxcode\LaravelSearch\Search;

class RebuildCommand extends Command
{
    protected $name = 'search:rebuild-index';
    protected $description = 'Rebuild the search index';
    /** @var \Nqxcode\LaravelSearch\Search */
    protected $search;

    public function __construct(Search $search)
    {
        parent::__construct();
        $this->search = $search;
    }

    public function fire()
    {
        $models = $this->search->config()->models();
        if (count($models)) {
            foreach ($models as $instance) {
                $all = $instance->all();
                foreach ($all as $model) {
                    $this->search->update($model);
                }
            }
            $this->info('Search index updated for all models!');
        } else {
            $this->error('No models found..');
        }
    }
}
