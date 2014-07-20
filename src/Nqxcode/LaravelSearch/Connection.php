<?php namespace Nqxcode\LaravelSearch;

use ZendSearch\Lucene\Lucene;
use ZendSearch\Exception\ExceptionInterface;

class Connection
{
    private $index;

    /**
     * Get descriptor for open index
     *
     * @return \ZendSearch\Lucene\SearchIndexInterface
     */
    public function getIndex()
    {
        return $this->index;
    }

    private $indexPath;

    /**
     * Get path to index
     *
     * @return mixed
     */
    public function getIndexPath()
    {
        return $this->indexPath;
    }

    /**
     *
     * Create connection to index
     *
     * @param $path
     * @throws \Exception
     */
    public function __construct($path)
    {
        $this->indexPath = $path;

        try {
            $this->index = Lucene::open($path);
        } catch (ExceptionInterface $e) {
            $this->index = Lucene::create($path);
        } catch (\Exception $e) {
            if (!file_exists($path)) {
                throw new \Exception(
                    "Couldn't connect to index of Zend Lucene. Directory '{$path}' doesn't exist.'"
                );
            }
            throw $e;
        }
    }

    /**
     * Destroy the entire index.
     *
     * @return bool
     */
    public function destroy()
    {
        if (!file_exists($this->indexPath) || !is_dir($this->indexPath)) {
            return false;
        }

        rmdir_recursive($this->indexPath);
        $this->index = null;

        return true;
    }
}
