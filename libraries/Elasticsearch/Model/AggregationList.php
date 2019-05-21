<?php

class Elasticsearch_Model_AggregationList
{
    private $aggregations_array;

    public static function build(array $aggregations = []): Elasticsearch_Model_AggregationList
    {
        $aggregationList = new Elasticsearch_Model_AggregationList();
        array_walk($aggregations, [$aggregationList, 'add']);
        return $aggregationList;
    }

    private function __construct()
    {
        $this->aggregations_array = [];
    }

    public function add(Elasticsearch_Model_Aggregation $aggregation): Elasticsearch_Model_AggregationList
    {
        $this->aggregations_array[$aggregation->getName()] = $aggregation->toArray();
        return $this;
    }

    public function toArray(): array {
        return $this->aggregations_array;
    }
}