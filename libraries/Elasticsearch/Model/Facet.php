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

    public function __construct(string $label, string $name, string $field, array $buckets)
    {
        $this->label = $label;
        $this->name = $name;
        $this->field = $field;
        $this->buckets = $buckets;
    }
}