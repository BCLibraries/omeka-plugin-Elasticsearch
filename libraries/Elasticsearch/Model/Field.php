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

    /**
     * @var string
     */
    private $regex;
    /**
     * @var string
     */
    private $format;

    public function __construct(
        string $name,
        string $label,
        string $type,
        string $origin,
        Elasticsearch_Model_Aggregation $aggregation = null,
        string $format = null,
        string $regex = null
    ) {
        $this->name = $name;
        $this->label = $label;
        $this->aggregation = $aggregation;
        $this->type = $type;
        $this->origin = $origin;
        $this->regex = $regex;
        $this->format = $format;
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

    public function getRegex(): ?string {
        return $this->regex;
    }

    public function getFormat(): ?string {
        return $this->format;
    }

    /**
     * @return Elasticsearch_Model_Aggregation
     */
    public function getAggregation(): Elasticsearch_Model_Aggregation
    {
        return $this->aggregation;
    }
}