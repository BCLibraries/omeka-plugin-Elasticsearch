<?php

require_once __DIR__ . '/AggregationList.php';

class Elasticsearch_Model_Query
{
    private $subqueries = [];
    private $filters = [];
    private $aggregations;

    public function __construct(array $subqueries, array $filters, Elasticsearch_Model_AggregationList $aggregations)
    {
        $this->subqueries = $subqueries;
        $this->filters = $filters;
        $this->aggregations = $aggregations;
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
            'aggregations' => $this->aggregations->toObject()
        ];
    }

    private function subQueryToArray(Elasticsearch_Model_SubQuery $subquery): array
    {
        return $subquery->toArray();
    }
}