<?php

class Elasticsearch_Model_Facet
{

    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $field;

    /**
     * @var Elasticsearch_Model_FacetBucket[]
     */
    public $buckets;

    /**
     * @var string
     */
    public $url;

    /** @var int */
    public $total;

    public function __construct(string $label, string $name, string $field, array $buckets)
    {
        $this->label = $label;
        $this->name = $name;
        $this->field = $field;
        $this->buckets = $buckets;
    }

    public function setUrl($url) {
        $this->url = $url;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }
}