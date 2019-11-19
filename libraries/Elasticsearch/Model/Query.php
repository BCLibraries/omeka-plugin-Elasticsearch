<?php

require_once __DIR__ . '/AggregationList.php';

class Elasticsearch_Model_Query
{
    private $query_array;

    public static function build(
        array $subqueries,
        array $filters,
        Elasticsearch_Model_AggregationList $aggregations
    ): Elasticsearch_Model_Query {
        return new Elasticsearch_Model_Query($subqueries, $filters, $aggregations);
    }

    public function __construct(
        array $subqueries,
        array $filters,
        Elasticsearch_Model_AggregationList $aggregations
    ) {
        $this->query_array = [
            'query' => [
                'bool' => [
                    'must' => array_map([$this, 'subQueryToArray'], $subqueries),
                    'filter' => array_map([$this, 'subQueryToArray'], $filters)
                ],
            ],
            'aggregations' => $aggregations->toObject(),
            'sort' => ['date']
        ];
    }

    public function toArray(): array
    {
        return $this->query_array;
    }

    public function offset(int $offset): Elasticsearch_Model_Query
    {
        $this->query_array['from'] = $offset;
        return $this;
    }

    public function limit(int $limit): Elasticsearch_Model_Query
    {
        $this->query_array['size'] = $limit;
        return $this;
    }

    public function sort(Elasticsearch_Model_Sort $sort): Elasticsearch_Model_Query
    {
        array_unshift($this->query_array['sort'], $sort->toArray());
        return $this;
    }

    public function addFilter(Elasticsearch_Model_SubQuery $filter)
    {
        $this->query_array['query']['bool']['filter'][] = $filter->toArray();
    }

    private function subQueryToArray(Elasticsearch_Model_SubQuery $subquery): array
    {
        return $subquery->toArray();
    }
}