<?php

use Elasticsearch\Client;

/**
 * Helper class that does the work of indexing site content.
 */
class Elasticsearch_Helper_Index
{

    /**
     * Creates an index.
     *
     * Use this to initialize mappings and other settings on the index.
     */
    public static function createIndex(): array
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
    public static function deleteIndex(): void
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
    public static function indexAll(): void
    {
        _log('INDEXING ALL');
        $docIndex = self::docIndex();
        _log('HAVE DOC INDEX');
        Elasticsearch_IntegrationManager::create($docIndex)->indexAll();
    }

    /**
     * Deletes all items from the index.
     *
     * @return void
     */
    public static function deleteAll(): void
    {
        $docIndex = self::docIndex();
        Elasticsearch_IntegrationManager::create($docIndex)->deleteAll();
    }

    /**
     * Pings the elasticsearch server to see if it is available or not.
     *
     * @return bool True if the server responded to the ping, false otherwise.
     */
    public static function ping(): bool
    {
        return self::client(['nobody' => true])->ping();
    }

    /**
     * Returns the elasticsearch client.
     *
     * @param array $options
     * @return Client
     */
    public static function client(array $options = []): Client
    {
        return Elasticsearch_Client::create($options);
    }

    /**
     * Returns the most recent jobs related to reindexing the site.
     *
     * @param array $options
     * @return array
     */
    public static function getReindexJobs(array $options = []): array
    {
        $limit = $options['limit'] ?? 10;
        $order = $options['order'] ?? 'id desc';
        $table = get_db()->getTable('Process');
        $select = $table->getSelect()->limit($limit)->order($order);
        $job_objects = $table->fetchObjects($select);

        $reindex_jobs = [];
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
     * @param $options
     * @return array
     * @throws Zend_Exception
     */
    public static function search($options)
    {
        $builder = new Elasticsearch_QueryBuilder();
        $query = $builder->build($_GET, Elasticsearch_Config::custom()->getAggregations());

        $acl = Zend_Registry::get('bootstrap')->getResource('Acl');
        if (!$acl->isAllowed(current_user(), 'Search', 'showNotPublic')) {
            $query->addFilter(Elasticsearch_Model_TermQuery::build('public', true));
        }
        return self::sendSearchQuery($query);
    }

    /**
     * @param string $aggregation_label
     * @return mixed
     * @throws Elasticsearch_Exception_BadQueryException
     * @throws Zend_Exception
     */
    public static function getMoreFacets(string $aggregation_label)
    {
        $builder = new Elasticsearch_QueryBuilder();

        $aggregation = Elasticsearch_Config::custom()->getAggregations($aggregation_label)[0];
        $aggregation->setSize(2000000);

        $query = $builder->build($_GET, [$aggregation]);
        $query->limit(0);

        $acl = Zend_Registry::get('bootstrap')->getResource('Acl');
        if (!$acl->isAllowed(current_user(), 'Search', 'showNotPublic')) {
            $query->addFilter(Elasticsearch_Model_TermQuery::build('public', true));
        }

        return self::sendSearchQuery($query);
    }

    /**
     * Returns the elasticsearch index name.
     *
     * @return string
     */
    public static function docIndex(): string
    {
        return get_option('elasticsearch_index');
    }

    /**
     * @param Elasticsearch_Model_Query $query
     * @return mixed
     */
    private static function sendSearchQuery(Elasticsearch_Model_Query $query)
    {
        $data_string = json_encode($query->toArray());

        //echo $data_string; exit();

        // create curl resource
        $ch = curl_init();

        // set url
        curl_setopt($ch, CURLOPT_URL, 'localhost/indipetae/_search');
        curl_setopt($ch, CURLOPT_PORT, 9200);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string)
            )
        );

        //return the transfer as a string
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // $output contains the output string
        $output = curl_exec($ch);

        return json_decode($output, true);
    }

}
