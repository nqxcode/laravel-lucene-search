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

        if (count($modelRepositories) > 0) {

            $modelsExist = false;
            foreach ($modelRepositories as $modelRepository) {
                $this->info('Creating index for "' . get_class($modelRepository) . '":');

                if (method_exists($modelRepository, 'allSearchable')) {
                    $all = $modelRepository->allSearchable();
                } else {
                    $all = $modelRepository->all();
                }

                $count = count($all);

                if ($count > 0) {

                    $modelsExist = true;

                    $progress = $this->getHelperSet()->get('progress');
                    $progress->start($this->getOutput(), $count);

                    foreach ($all as $model) {
                        $search->update($model);
                        $progress->advance();
                    }
                    $progress->finish();
                } else {
                    $this->comment(' No available models found. ');
                }
            }

            if ($modelsExist) {
                $this->info('Operation is fully complete!');
            }
        } else {
            $this->error('No models found in config.php file..');
        }
    }
}
