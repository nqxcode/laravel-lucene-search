<?php  namespace Nqxcode\LuceneSearch\Console;

use Illuminate\Console\Command;
use Nqxcode\LuceneSearch\Search;

use App;
use Config;

class RebuildCommand extends Command
{
    protected $name = 'search:rebuild';
    protected $description = 'Rebuild the search index';

    public function __construct()
    {
        parent::__construct();
    }

    public function fire()
    {
        if (is_dir(Config::get('laravel-lucene-search::index.path'))) {
            $this->call('search:clear');
        }

        /** @var Search $search */
        $search = App::make('search');

        $modelRepositories = $search->config()->modelRepositories();

        if (count($modelRepositories)) {
            foreach ($modelRepositories as $modelRepository) {
                if (method_exists($modelRepository, 'allSearchable')) {
                    $all = $modelRepository->allSearchable();
                } else {
                    $all = $modelRepository->all();
                }
                foreach ($all as $model) {
                    $search->update($model);
                }
            }
            $this->info('Search index is updated.');
        } else {
            $this->error('No models found in config.php file..');
        }
    }
}
