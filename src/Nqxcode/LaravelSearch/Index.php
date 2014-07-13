<?php namespace Nqxcode\LaravelSearch;

use Illuminate\Database\Eloquent\Model;
use Nqxcode\LaravelSearch\Index\Configurator;
use Nqxcode\LaravelSearch\Index\Connection;
use Nqxcode\LaravelSearch\Query\Builder as QueryBuilder;
use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Index\Term;
use ZendSearch\Lucene\Search\Query\MultiTerm;
use ZendSearch\Lucene\Document\Field;

class Index
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
     * @var Configurator
     */
    private $configurator;

    /**
     * @return \Nqxcode\LaravelSearch\Index\Configurator
     */
    public function configurator()
    {
        return $this->configurator;
    }

    /**
     * Create index instance.
     *
     * @param Index\Connection $connection
     * @param Configurator $configurator
     */
    public function __construct(
        Connection $connection,
        Configurator $configurator
    )
    {
        $this->connection = $connection;
        $this->configurator = $configurator;
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
     * Find hit for model in index.
     *
     * @param Model $model
     * @return array|\ZendSearch\Lucene\Search\QueryHit
     */
    private function find(Model $model)
    {
        $query = new MultiTerm();
        list($name, $value) = $this->configurator->getModelPrivateKey($model);

        $query->addTerm(new Term($value, $name), true);

        list($name, $value) = $this->configurator->getModelClassHash($model);
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

        // Create document for model.
        // Create new document.
        $doc = new Document();

        list($name, $value) = $this->configurator->getModelPrivateKey($model);

        // Add private key.
        $doc->addField(Field::keyword($name, $value));

        list($name, $value) = $this->configurator->getModelClassHash($model);

        // Add class hash for model.
        $doc->addField(Field::Keyword($name, $value));

        $fields = $this->configurator->getModelFields($model);

        // Add fields to document to be indexed (but not stored).
        foreach ($fields as $field => $options) {
            $doc->addField(Field::unStored(trim($field), strip_tags(trim($model->{trim($field)}))));
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
        $hits = $this->find($model);
        foreach ($hits as $hit) {
            $this->index()->delete($hit->id); // delete document from index.
        }
    }

    /**
     * @param $value
     * @param $field
     * @param array $options
     * @return $this
     */
    public function search($value, $field = '*', array $options = array())
    {
        $query = new QueryBuilder($this);
        $this->last_query = $query;

        return $query->build($value, $field, $options);
    }

    public function lastQuery()
    {
        return $this->last_query->last_query;
    }
}
