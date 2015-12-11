<?php namespace Nqxcode\LuceneSearch\Index;

use Nqxcode\LuceneSearch\Analyzer\Config as AnalyzerConfig;
use ZendSearch\Lucene\Lucene;
use ZendSearch\Exception\ExceptionInterface;

class Connection
{
    /** @var \ZendSearch\Lucene\SearchIndexInterface */
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
     * @param AnalyzerConfig $config
     * @throws \Exception
     */
    public function __construct($path, AnalyzerConfig $config)
    {
        $config->setDefaultAnalyzer();

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
}
