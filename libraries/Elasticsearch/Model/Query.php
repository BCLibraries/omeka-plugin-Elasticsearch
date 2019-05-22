<?php

require_once __DIR__ . '/AggregationList.php';

class Elasticsearch_Model_Query
{
    private $subqueries = [];
    private $filters = [];
    private $aggregations;

    /**
     * @var int
     */
    private $offset;

    /**
     * @var int
     */
    private $limit;

    public function __construct(
        array $subqueries,
        array $filters,
        Elasticsearch_Model_AggregationList $aggregations,
        int $offset = 0,
        int $limit = 20
    ) {
        $this->subqueries = $subqueries;
        $this->filters = $filters;
        $this->aggregations = $aggregations;
        $this->offset = $offset;
        $this->limit = $limit;
    }

    public function toArray(): array
    {
        return [
            'query' => [
                'bool' => [
                    'must' => array_map([$this, 'subQueryToArray'], $this->subqueries),
                    'filter' => array_map([$this, 'subQueryToArray'], $this->filters)
                ]
            ],
            'aggregations' => $this->aggregations->toObject(),
            'from' => $this->offset,
            'size' => $this->limit
        ];
    }

    private function subQueryToArray(Elasticsearch_Model_SubQuery $subquery): array
    {
        return $subquery->toArray();
    }
}