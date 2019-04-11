<?php

class Elasticsearch_Model_Field
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $type;

    /**
     * @var Elasticsearch_Model_Aggregation
     */
    private $aggregation;

    /**
     * @var string
     */
    private $origin;

    public function __construct(
        string $name,
        string $label,
        string $type,
        string $origin,
        Elasticsearch_Model_Aggregation $aggregation = null
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->aggregation = $aggregation;
        $this->type = $type;
        $this->origin = $origin;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getType(): string {
        return $this->type;
    }

    public function getOrigin(): string {
        return $this->origin;
    }

    /**
     * @return Elasticsearch_Model_Aggregation
     */
    public function getAggregation(): Elasticsearch_Model_Aggregation
    {
        return $this->aggregation;
    }
}