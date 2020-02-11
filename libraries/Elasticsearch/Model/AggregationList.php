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
        $this->addCount($aggregation);
    }

    public function toObject(): stdClass
    {
        return $this->aggregations;
    }

    private function addCount(Elasticsearch_Model_Aggregation $aggregation)
    {
        $count_label = "{$aggregation->getName()}_count";
        $this->aggregations->{$count_label} = ['cardinality' => ['field' => $aggregation->getField()]];
    }
}