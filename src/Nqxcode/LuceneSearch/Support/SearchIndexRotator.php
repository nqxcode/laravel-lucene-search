<?php namespace Nqxcode\LuceneSearch\Support;

use File;

/**
 * Class SearchIndexRotator
 * @package Nqxcode\LuceneSearch\Support
 */
class SearchIndexRotator
{
    private $currentIndexPath;
    private $newIndexPath;
    private $previewIndexPath;

    public function __construct($currentPath, $newPath, $previewPath)
    {
        $this->currentIndexPath = $currentPath;
        $this->newIndexPath = $newPath;
        $this->previewIndexPath = $previewPath;
    }

    /**
     * @return mixed
     */
    public function getNewIndexPath()
    {
        return $this->newIndexPath;
    }

    public function rotate()
    {
        File::deleteDirectory($this->previewIndexPath);

        File::copyDirectory($this->currentIndexPath, $this->previewIndexPath);
        File::cleanDirectory($this->currentIndexPath);

        File::copyDirectory($this->newIndexPath, $this->currentIndexPath);
        File::deleteDirectory($this->newIndexPath);
    }
}
