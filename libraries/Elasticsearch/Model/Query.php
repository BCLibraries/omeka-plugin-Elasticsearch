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
            'aggregations' => Elasticsearch_Model_Aggregations::getAggregationsParams()
        ];

        if (empty($terms)) {
            $must_query = ['match_all' => new \stdClass()];
        } else {
            $must_query = [
                'query_string' => [
                    'query' => $terms,
                    'default_operator' => 'OR'
                ],
            ];
        }
        $body['query']['bool']['must'] = [$must_query];

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

    public function sort($sort): void
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

    public function export(): array
    {
        return $this->params;
    }

    /**
     * Given an array of key/value pairs defining the facets of the search that the
     * user would like to drill down into, this function returns an array of filters
     * that can be used in an elasticsearch query to narrow the search results.
     *
     * @param $input_facets
     * @return array
     */
    public static function getFacetFilters($input_facets): array
    {
        $all_facets = Elasticsearch_Config::custom()->getAggregations();
        $filters = [];
        foreach ($all_facets as $label => $facet) {
            $name = $facet->getName();
            if (isset($input_facets[$name])) {
                $filters[] = ['term' => [$name => $input_facets[$name]]];
            }
        }
        return $filters;
    }
}