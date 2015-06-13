<?php namespace Nqxcode\LuceneSearch;

use Illuminate\Database\Eloquent\Model;
use Nqxcode\LuceneSearch\Highlighting\Html;
use Nqxcode\LuceneSearch\Model\Config;
use Nqxcode\LuceneSearch\Query\Builder as QueryBuilder;
use Nqxcode\LuceneSearch\Highlighting\Html as Highlighter;
use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Index\Term;
use ZendSearch\Lucene\Search\Query\MultiTerm;
use ZendSearch\Lucene\Document\Field;

use App;

/**
 * Class Search
 *
 * @package Nqxcode\LuceneSearch
 */
class Search
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * Get descriptor for open index.
     *
     * @return \ZendSearch\Lucene\SearchIndexInterface
     */
    public function index()
    {
        return $this->connection->getIndex();
    }

    /**
     * Model configurator.
     *
     * @var Config
     */
    private $config;

    /**
     * @return \Nqxcode\LuceneSearch\Model\Config
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * Create index instance.
     *
     * @param Connection $connection
     * @param Config $config
     */
    public function __construct(Connection $connection, Config $config)
    {
        $this->connection = $connection;
        $this->config = $config;
    }

    /**
     * Destroy the entire index.
     *
     * @return bool
     */
    public function destroy()
    {
        $this->connection->destroy();
    }

    /**
     * Find query hits for model in index.
     *
     * @param Model $model
     * @return array|\ZendSearch\Lucene\Search\QueryHit
     */
    private function findHits(Model $model)
    {
        // Build query for finding of model's hits.
        $query = new MultiTerm();

        // Add model's class UID.
        list($name, $value) = $this->config->privateKeyPair($model);
        $query->addTerm(new Term($value, $name), true);

        // Add class uid for identification of model's class.
        list($name, $value) = $this->config->classUidPair($model);
        $query->addTerm(new Term($value, $name), true);

        return $this->index()->find($query);
    }

    /**
     * Update document in index for model
     *
     * @param Model $model
     */
    public function update(Model $model)
    {
        // Remove any existing documents for model.
        $this->delete($model);

        // Create new document for model.
        $doc = new Document();

        list($name, $value) = $this->config->privateKeyPair($model);

        // Add private key.
        $doc->addField(Field::keyword($name, $value));

        // Add model's class UID.
        list($name, $value) = $this->config->classUidPair($model);

        // Add class uid for identification of model's class.
        $doc->addField(Field::Keyword($name, $value));

        $fields = $this->config->fields($model);

        // Add fields to document to be indexed (but not stored).
        foreach ($fields as $field) {
            $doc->addField(Field::unStored(trim($field), strip_tags(trim($model->{trim($field)}))));
        }

        $optionalAttributes = $this->config->optionalAttributes($model);

        // Add optional attributes to document to be indexed (but not stored).
        foreach ($optionalAttributes as $fieldName => $fieldValue) {
            $doc->addField(Field::unStored(trim($fieldName), strip_tags(trim($fieldValue))));
        }

        // Add document to index.
        $this->index()->addDocument($doc);
    }

    /**
     * Delete document for model from index.
     *
     * @param Model $model
     */
    public function delete(Model $model)
    {
        // Find all hits for model.
        $hits = $this->findHits($model);
        foreach ($hits as $hit) {
            $this->index()->delete($hit->id); // delete document from index by ID of hit.
        }
    }

    /**
     * All calls of inaccessible methods send to query builder object.
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $queryBuilder = App::make(QueryBuilder::class);
        return call_user_func_array([$queryBuilder, $name], $arguments);
    }

    /**
     * Highlight matches in html fragment.
     *
     * @param string $html
     * @return string
     */
    public function highlight($html)
    {
        /** @var Html $highlighter */
        $highlighter = App::make(Highlighter::class);
        return $highlighter->highlight($html);
    }
}
