<?php

/**
 * Helper class that does the work of indexing site content.
 */
class Elasticsearch_Helper_Index
{

    /**
     * Creates an index.
     *
     * Use this to initialize mappings and other settings on the index.
     *
     * @return void
     */
    public static function createIndex()
    {
        $params = Elasticsearch_Model_IndexParams::getIndexParams(self::docIndex());
        return self::client()->indices()->create($params);
    }

    /**
     * Deletes the elasticsearch index and all documents in it.
     *
     * Assumes that index auto-creation is enabled so that when items are re-indexed,
     * the index will be created automatically.
     */
    public static function deleteIndex()
    {
        $params = ['index' => self::docIndex()];
        if (self::client(['nobody' => true])->indices()->exists($params)) {
            self::client()->indices()->delete($params);
        }
    }

    /**
     * Indexes all items and integrated addons.
     *
     * @return void
     */
    public static function indexAll()
    {
        $docIndex = self::docIndex();
        Elasticsearch_IntegrationManager::create($docIndex)->indexAll();
    }

    /**
     * Deletes all items from the index.
     *
     * @return void
     */
    public static function deleteAll()
    {
        $docIndex = self::docIndex();
        Elasticsearch_IntegrationManager::create($docIndex)->deleteAll();
    }

    /**
     * Pings the elasticsearch server to see if it is available or not.
     *
     * @return bool True if the server responded to the ping, false otherwise.
     */
    public static function ping()
    {
        return self::client(['nobody' => true])->ping();
    }

    /**
     * Returns the elasticsearch client.
     *
     * @return \Elasticsearch\Client
     */
    public static function client(array $options = array())
    {
        return Elasticsearch_Client::create($options);
    }

    /**
     * Returns the most recent jobs related to reindexing the site.
     *
     * @return array
     */
    public static function getReindexJobs(array $options = array())
    {
        $limit = isset($options['limit']) ? $options['limit'] : 10;
        $order = isset($options['order']) ? $options['order'] : 'id desc';
        $table = get_db()->getTable('Process');
        $select = $table->getSelect()->limit($limit)->order($order);
        $job_objects = $table->fetchObjects($select);

        $reindex_jobs = array();
        foreach ($job_objects as $job_object) {
            // Because job args are serialized to a string using some combination of PHP serialize() and json_encode(),
            // just do a simple string search rather than try to deal with that.
            if (!empty($job_object->args) && strrpos($job_object->args, 'Elasticsearch_Job_Reindex') !== false) {
                $reindex_jobs[] = $job_object;
            }
        }

        return $reindex_jobs;
    }

    /**
     * Executes a search query on an index
     *
     * @param $query
     * @param $options
     * @return array
     */
    public static function search($options)
    {
        if (!isset($options['query']) || !is_array($options['query'])) {
            throw new Exception("Query parameter is required to execute elasticsearch query.");
        }
        $query = $options['query'];
        $offset = isset($options['offset']) ? $options['offset'] : 0;
        $limit = isset($options['limit']) ? $options['limit'] : 20;
        $terms = isset($options['query']['q']) ? $options['query']['q'] : '';
        $facets = isset($options['query']['facets']) ? $options['query']['facets'] : [];
        $sort = isset($options['sort']) ? $options['sort'] : null;

        $acl = Zend_Registry::get('bootstrap')->getResource('Acl');
        $query = new Elasticsearch_Model_Query($terms, $facets, $offset, $limit, self::docIndex(), $acl);
        $query->sort($sort);
        $params = $query->export();

        _log("elasticsearch search params:\n" . json_encode($params, JSON_PRETTY_PRINT), Zend_Log::DEBUG);

        return self::client()->search($params);
    }

    /**
     * Returns the elasticsearch index name.
     *
     * @return string
     */
    public static function docIndex()
    {
        return get_option('elasticsearch_index');
    }

}
