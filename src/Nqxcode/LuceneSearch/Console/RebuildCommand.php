<?php namespace Nqxcode\LuceneSearch\Console;

use Illuminate\Console\Command;
use Nqxcode\LuceneSearch\Search;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\NullOutput;

use App;
use Config;

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

        $modelRepositories = $search->config()->repositories();

        if (count($modelRepositories) > 0) {
            foreach ($modelRepositories as $modelRepository) {
                $this->info('Creating index for model: "' . get_class($modelRepository) . '"');

                $all = $modelRepository->all();

                $count = count($all);

                if ($count > 0) {
                    $progress = new ProgressBar($this->getOutput(), $count);
                    foreach ($all as $model) {
                        $search->update($model);
                        $progress->advance();
                    }
                    $progress->finish();

                } else {
                    $this->comment(' No available models found. ');
                }
            }
            $this->info(PHP_EOL . 'Operation is fully complete!');
        } else {
            $this->error('No models found in config.php file..');
        }
    }
}
