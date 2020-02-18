<?php namespace Nqxcode\LuceneSearch\Model;

use App;
use Queue;

/**
 * Class SearchObserver
 * @package LuceneSearch\Model
 */
class SearchObserver
{
    /** @var bool */
    private static $enabled = true;

    /** @var string|null */
    private static $queue = null;

    /**
     * @param bool $enabled
     */
    public static function setEnabled($enabled)
    {
        self::$enabled = $enabled;
    }

    /**
     * @param bool $queue
     */
    public static function setQueue($queue)
    {
        self::$queue = $queue;
    }

    public function saved($model)
    {
        if (self::$enabled) {
            if (self::$queue) {
                Queue::push(
                    'Nqxcode\LuceneSearch\Job\UpdateSearchIndex',
                    [
                        'modelClass' => get_class($model),
                        'modelKey' => $model->getKey()
                    ],
                    self::$queue
                );

            } else {
                App::offsetGet('search')->update($model);
            }
        }
    }

    public function deleting($model)
    {
        if (self::$enabled) {
            if (self::$queue) {
                Queue::push(
                    'Nqxcode\LuceneSearch\Job\DeleteSearchIndex',
                    [
                        'modelClass' => get_class($model),
                        'modelKey' => $model->getKey()
                    ],
                    self::$queue
                );

            } else {
                App::offsetGet('search')->delete($model);
            }
        }
    }
}
