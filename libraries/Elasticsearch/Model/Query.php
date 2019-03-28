<?php

class Elasticsearch_Model_Query
{
    private $params;
    private $body;

    /**
     * Elasticsearch_Model_Query constructor.
     *
     * @param string $terms
     * @param array $facets
     * @param int $offset
     * @param int $limit
     * @param string $index
     * @param Zend_Acl $acl
     * @throws Zend_Exception
     */
    public function __construct(
        $terms,
        $facets,
        $offset,
        $limit,
        $index,
        $acl
    ) {
        // Main body of query
        $body = [
            'query' => ['bool' => []],
            'aggregations' => Elasticsearch_Model_Aggregations::getAggregations()
        ];

        // Add must query
        if (empty($terms)) {
            $must_query = ['match_all' => new \stdClass()];
        } else {
            $must_query = [
                'query_string' => [
                    'query' => $terms,
                    'default_operator' => 'OR'
                ]
            ];
        }
        $body['query']['bool']['must'] = $must_query;

        // Add filters
        $showNotPublic = $acl->isAllowed(current_user(), 'Search', 'showNotPublic');
        $filters = self::getFacetFilters($facets);
        if (!$showNotPublic) {
            $filters[] = ['term' => ['public' => true]];
        }
        if (count($filters) > 0) {
            $body['query']['bool']['filter'] = $filters;
        }

        $this->body = $body;

        $this->params = [
            'index' => $index,
            'from' => $offset,
            'size' => $limit,
            'body' => $this->body
        ];
    }

    public function sort($sort)
    {
        if (isset($sort['field'])) {
            $this->body['sort'] = [
                [
                    $sort['field'] => $sort['dir'] ?? 'asc'
                ]
            ];
            $this->body['track_scores'] = true;
        }
    }

    public function export()
    {
        return $this->params;
    }

    /**
     * Given an array of key/value pairs defining the facets of the search that the
     * user would like to drill down into, this function returns an array of filters
     * that can be used in an elasticsearch query to narrow the search results.
     *
     * @param $facets
     * @return array
     */
    public static function getFacetFilters($facets)
    {
        $filters = [];
        if (isset($facets['tags'])) {
            $filters[] = ['terms' => ['tags.keyword' => $facets['tags']]];
        }
        if (isset($facets['collection'])) {
            $filters[] = ['term' => ['collection.keyword' => $facets['collection']]];
        }
        if (isset($facets['exhibit'])) {
            $filters[] = ['term' => ['exhibit.keyword' => $facets['exhibit']]];
        }
        if (isset($facets['itemtype'])) {
            $filters[] = ['term' => ['itemtype' => $facets['itemtype']]];
        }
        if (isset($facets['resulttype'])) {
            $filters[] = ['term' => ['resulttype' => $facets['resulttype']]];
        }
        if (isset($facets['featured'])) {
            $filters[] = ['term' => ['featured' => $facets['featured']]];
        }
        return $filters;
    }
}