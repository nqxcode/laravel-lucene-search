<?php namespace Nqxcode\LuceneSearch\Locker;

/**
 * Class Locker
 * @package Nqxcode\LuceneSearch\Index
 */
class Locker
{
    /**
     * @var string
     */
    private $lockFilePath;

    /**
     * @var resource
     */
    private $lockFileHandler;

    /**
     * @param string $lockFilePath
     */
    public function __construct($lockFilePath)
    {
        $this->lockFilePath = $lockFilePath;
    }


    public function __destruct()
    {
        $this->unlock();
    }


    /**
     * Lock status file.
     *
     * @return bool
     * @throws \RuntimeException
     */
    public function lock()
    {
        $dir = pathinfo($this->lockFilePath, PATHINFO_DIRNAME);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $this->lockFileHandler = fopen($this->lockFilePath, 'w');
        if (!$this->lockFileHandler) {
            throw new \RuntimeException("Can't create lock file");
        }

        return flock($this->lockFileHandler, LOCK_EX | LOCK_NB);
    }


    /**
     * Unlock status file.
     */
    public function unlock()
    {
        if (null !== $this->lockFileHandler) {
            flock($this->lockFileHandler, LOCK_UN);
            fclose($this->lockFileHandler);
            $this->lockFileHandler = null;
        }
    }


    /**
     * Do locked task.
     *
     * @param callable $callback
     * @throws \RuntimeException
     */
    public function doLocked(callable $callback)
    {
        if ($this->lock()) {
            call_user_func($callback);
            $this->unlock();
        }
    }


    /**
     * Check if locked.
     *
     * @return bool
     */
    public function isLocked()
    {
        if ($this->lock()) {
            $locked = false;
            $this->unlock();
        } else {
            $locked = true;
        }

        return $locked;
    }
}
