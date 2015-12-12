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

        $this->call('search:clear');

        /** @var Search $search */
        $search = App::make('search');

        $modelRepositories = $search->config()->repositories();

        if (count($modelRepositories) > 0) {
            foreach ($modelRepositories as $modelRepository) {
                $this->info('Creating index for model: "' . get_class($modelRepository) . '"');

                $count = $modelRepository->count();

                if ($count === 0) {
                    $this->comment(' No available models found. ');
                    continue;
                }

                $progress = new ProgressBar($this->getOutput(), $count);
                $progress->start();

                $modelRepository->chunk(1000, function ($chunk) use ($progress, $search) {
                    foreach ($chunk as $model) {
                        $search->update($model);
                        $progress->advance();
                    }
                });

                $progress->finish();
            }
            $this->info(PHP_EOL . 'Operation is fully complete!');
        } else {
            $this->error('No models found in config.php file..');
        }
    }
}
