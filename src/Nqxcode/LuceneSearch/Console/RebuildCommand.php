<?php namespace Nqxcode\LuceneSearch\Console;

use App;
use Config;
use File;
use Illuminate\Console\Command;
use Nqxcode\LuceneSearch\Locker\Locker;
use Nqxcode\LuceneSearch\Search;
use Nqxcode\LuceneSearch\Support\SearchIndexRotator;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;
use Queue;

class RebuildCommand extends Command
{
    protected $name = 'search:rebuild';
    protected $description = 'Rebuild the search index';

    /**
     * @var Search
     */
    private $search;

    /**
     * @var SearchIndexRotator
     */
    private $searchIndexRotator;

    protected function getOptions()
    {
        return [
            ['--force', null, InputOption::VALUE_NONE, 'Rebuild of search index with pre-cleaning', null],
        ];
    }

    public function fire()
    {
        if (!$this->option('verbose')) {
            $this->output = new NullOutput;
        }

        $lockFilePath = storage_path('laravel-lucene-search/rebuild.lock');

        $locker = new Locker($lockFilePath);

        if ($locker->isLocked()) {
            $this->error('Rebuild is already running!');
        }

        $locker->doLocked(function () {
            if ($this->option('force')) {
                $this->call('search:clear');
                $this->rebuild();

            } else {
                $this->rebuild();
            }
        });
    }

    private function rebuild()
    {
        /** @var Search $search */
        $this->search = App::make('search');

        $this->searchIndexRotator = App::make('search.index.rotator');
        $queue = Config::get('laravel-lucene-search::queue');
        $indexPath = Config::get('laravel-lucene-search::index.path');

        $modelRepositories = $this->search->config()->repositories();

        if (count($modelRepositories) > 0) {
            foreach ($modelRepositories as $modelRepository) {
                $this->info('Creating index for model: "' . get_class($modelRepository) . '"');

                $count = $modelRepository->count();

                if ($count === 0) {
                    $this->comment(' No available models found.');
                    continue;
                }

                $chunkCount = Config::get('laravel-lucene-search::chunk');
                $progress = new ProgressBar($this->getOutput(), $count / $chunkCount);
                $progress->start();

                $modelRepository->chunk($chunkCount, function ($chunk) use ($progress, $queue) {
                    $newIndexPath = $this->searchIndexRotator->getNewIndexPath();

                    if ($queue) {
                        Queue::push(
                            'Nqxcode\LuceneSearch\Job\MassUpdateSearchIndex',
                            [
                                'modelClass' => get_class($chunk[0]),
                                'modelKeys' => $chunk->lists($chunk[0]->getKeyName()),
                                'indexPath' => $newIndexPath,
                            ],
                            $queue);

                    } else {
                        Config::set('laravel-lucene-search::index.path', $newIndexPath);
                        foreach ($chunk as $model) {
                            $this->search->update($model);
                        }
                    }

                    $progress->advance();
                });

                $progress->finish();
                $this->info(PHP_EOL);
            }
            $this->info(PHP_EOL . 'Operation is fully complete!');
        } else {
            $this->error('No models found in config.php file..');
        }

        $this->search->destroyConnection();

        if ($queue) {
            Queue::push('Nqxcode\LuceneSearch\Job\RotateSearchIndex', $queue);

        } else {
            $this->searchIndexRotator->rotate();
        }

        Config::set('laravel-lucene-search::index.path', $indexPath);
    }
}
