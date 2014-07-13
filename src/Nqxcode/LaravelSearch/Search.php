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
     * Find hit for model in index.
     *
     * @param Model $model
     * @return array|\ZendSearch\Lucene\Search\QueryHit
     */
    private function find(Model $model)
    {
        $query = new MultiTerm();
        list($name, $value) = $this->config->getModelPrivateKey($model);

        $query->addTerm(new Term($value, $name), true);

        list($name, $value) = $this->config->getModelClassHash($model);
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

        list($name, $value) = $this->config->getModelPrivateKey($model);

        // Add private key.
        $doc->addField(Field::keyword($name, $value));

        list($name, $value) = $this->config->getModelClassHash($model);

        // Add class hash for model.
        $doc->addField(Field::Keyword($name, $value));

        $fields = $this->config->getModelFields($model);

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
        return $this->last_query->last_query_string;
    }
}
