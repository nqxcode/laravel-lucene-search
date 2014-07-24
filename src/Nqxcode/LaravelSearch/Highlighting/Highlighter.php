<?php namespace Nqxcode\LaravelSearch\Highlighting;

use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Search\Highlighter\HighlighterInterface;

/**
 * Class Highlighter
 *
 * Provides a functionality for illumination of words.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 */
class Highlighter implements HighlighterInterface
{
    /**
     * HTML document for highlighting
     *
     * @var \ZendSearch\Lucene\Document\HTML
     */
    protected $_doc;

    /**
     * {@inheritdoc}
     */
    public function setDocument(Document\HTML $document)
    {
        $this->_doc = $document;
    }

    /**
     * {@inheritdoc}
     */
    public function getDocument()
    {
        return $this->_doc;
    }

    /**
     * {@inheritdoc}
     */
    public function highlight($words)
    {
        $this->_doc->highlightExtended($words, array($this, 'applyColour'), array());
    }

    public function applyColour($words)
    {
        return "<span class='highlight'>{$words}</span>";
    }
}
