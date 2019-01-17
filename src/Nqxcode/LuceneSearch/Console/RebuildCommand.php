<?php namespace Nqxcode\LuceneSearch\Console;

use App;
use Config;
use Illuminate\Console\Command;
use Nqxcode\LuceneSearch\Locker\Locker;
use Nqxcode\LuceneSearch\Search;
use Symfony\Component\Console\Helper\ProgressBar;
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

        $lockFilePath = sys_get_temp_dir()
            . DIRECTORY_SEPARATOR
            . 'laravel-lucene-search'
            . DIRECTORY_SEPARATOR
            . 'rebuild.lock';

        $locker = new Locker($lockFilePath);

        if ($locker->isLocked()) {
            $this->error('Rebuild is already running!');
        }

        $locker->doLocked(function () {
            $oldIndexPath = \Config::get('laravel-lucene-search::index.path');
            $newIndexPath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'laravel-lucene-search' . DIRECTORY_SEPARATOR . 'index';

            \Config::set('laravel-lucene-search::index.path', $newIndexPath);

            /** @var Search $search */
            $search = App::make('search');

            $modelRepositories = $search->config()->repositories();

            if (count($modelRepositories) > 0) {
                foreach ($modelRepositories as $modelRepository) {
                    $this->info('Creating index for model: "' . get_class($modelRepository) . '"');

                    $count = $modelRepository->count();

                    if ($count === 0) {
                        $this->comment(' No available models found.');
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
                    $this->info(PHP_EOL);
                }
                $this->info(PHP_EOL . 'Operation is fully complete!');
            } else {
                $this->error('No models found in config.php file..');
            }

            \File::cleanDirectory($oldIndexPath);
            \File::copyDirectory($newIndexPath, $oldIndexPath);

            \Config::set('laravel-lucene-search::index.path', $oldIndexPath);
        });
    }
}
