<?php namespace Nqxcode\LaravelSearch;

use Illuminate\Database\Eloquent\Model;
use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Index\Term;
use ZendSearch\Lucene\Search\Query\MultiTerm;
use ZendSearch\Lucene\Document\Field;

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
     * @return \Nqxcode\LaravelSearch\Config
     */
    public function config()
    {
        return $this->config;
    }

    /**
     * @var
     */
    private $queryBuilder;

    /**
     * Create index instance.
     *
     * @param Connection $connection
     * @param Config $configurator
     */
    public function __construct(Connection $connection, Config $configurator)
    {
        $this->connection = $connection;
        $this->config = $configurator;
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
        list($name, $value) = $this->config->privateKey($model);
        $query->addTerm(new Term($value, $name), true);

        // Add class uid for identification of model's class.
        list($name, $value) = $this->config->classUid($model);
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

        if (method_exists($model, 'isAvailableForSearching')) {
            // The model is available to an indexing?
            if (!$model->{'isAvailableForSearching'}()) {
                return;
            }
        }

        // Create new document for model.
        $doc = new Document();

        list($name, $value) = $this->config->privateKey($model);

        // Add private key.
        $doc->addField(Field::keyword($name, $value));

        // Add model's class UID.
        list($name, $value) = $this->config->classUid($model);

        // Add class uid for identification of model's class.
        $doc->addField(Field::Keyword($name, $value));

        $fields = $this->config->fields($model);

        // Add fields to document to be indexed (but not stored).
        foreach ($fields as $field) {
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
        // Find all hits for model.
        $hits = $this->findHits($model);
        foreach ($hits as $hit) {
            $this->index()->delete($hit->id); // delete document from index by hit's ID.
        }
    }

    /**
     * All calls of inaccessible methods send to QueryBuilder object.
     *
     * @param $name
     * @param $arguments
     * @return mixed
     */
    public function __call($name, $arguments)
    {
        $this->queryBuilder = new QueryBuilder($this);
        return call_user_func_array([$this->queryBuilder, $name], $arguments);
    }


    public function lastQuery()
    {
        return $this->queryBuilder->last_query_string;
    }
}
