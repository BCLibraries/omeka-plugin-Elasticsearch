<?php

class Elasticsearch_Model_AggregationList
{
    /**
     * @var stdClass
     */
    private $aggregations;

    public function __construct(array $aggregations = [])
    {
        $this->aggregations = new stdClass();
        foreach ($aggregations as $aggregation) {
            $this->add($aggregation);
        }
    }

    public function add(Elasticsearch_Model_Aggregation $aggregation): void
    {
        $this->aggregations->{$aggregation->getName()} = $aggregation->toArray();
    }

    public function toObject(): stdClass {
        return  $this->aggregations;
    }
}