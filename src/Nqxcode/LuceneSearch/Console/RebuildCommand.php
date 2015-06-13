<?php namespace Nqxcode\LuceneSearch\Console;

use Illuminate\Console\Command;
use Nqxcode\LuceneSearch\Search;

use App;
use Config;
use Symfony\Component\Console\Output\NullOutput;

class RebuildCommand extends Command
{
    protected $name = 'search:rebuild';
    protected $description = 'Rebuild the search index';

    public function fire()
    {
        if (!$this->option('verbose')) {
            $this->output = new NullOutput;
        }

        if (is_dir(Config::get('laravel-lucene-search.index.path'))) {
            $this->call('search:clear');
        }

        /** @var Search $search */
        $search = App::make('search');

        $modelRepositories = $search->config()->modelRepositories();

        if (count($modelRepositories) > 0) {
            foreach ($modelRepositories as $modelRepository) {
                $this->info('Creating index for model: "' . get_class($modelRepository) . '"');

                $all = $modelRepository->all();

                $count = count($all);

                if ($count > 0) {
                    /** @var \Symfony\Component\Console\Helper\ProgressBar $progress */
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
            $this->info('Operation is fully complete!');
        } else {
            $this->error('No models found in config.php file..');
        }
    }
}
