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
        $this->call('search:clear');

        $modelRepositories = $this->search->config()->modelRepositories();

        if (count($modelRepositories)) {
            foreach ($modelRepositories as $modelRepository) {
                if (method_exists($modelRepository, 'allSearchable')) {
                    $all = $modelRepository->allSearchable();
                } else {
                    $all = $modelRepository->all();
                }
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
