<?php

class Elasticsearch_Helper_ConfigReader
{
    /**
     * @var Elasticsearch_Model_Field[]
     */
    private $fields;

    /**
     * @var Elasticsearch_Model_Aggregation[]
     */
    private $aggregations;

    /**
     * @var string
     */
    private $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    /**
     * @return Elasticsearch_Model_Aggregation[]
     */
    public function getAggregations(): array
    {
        if (!isset($this->aggregations)) {
            $this->load();
        }
        return $this->aggregations;
    }

    /**
     * @return Elasticsearch_Model_Field[]
     */
    public function getFields(): array
    {
        if (!isset($this->fields)) {
            $this->load();
        }
        return $this->fields;
    }

    private function load(): void
    {
        if (! file_exists($this->file)) {

        }
        $json = json_decode(file_get_contents($this->file));
        $this->fields = [];
        $this->aggregations = [];
        foreach ($json->fields as $field) {
            $this->loadFieldFromJSON($field);
        }
    }

    private function loadFieldFromJSON($field_json): void
    {
        $aggregation = $this->buildAggregation($field_json);
        $this->buildField($field_json, $aggregation);
    }

    private function buildAggregation($field_json): ?Elasticsearch_Model_Aggregation
    {
        if (!isset($field_json->aggregation)) {
            return null;
        }
        $agg_json = $field_json->aggregation;
        $agg = new Elasticsearch_Model_Aggregation($agg_json->name, $agg_json->label, $field_json->name);
        $this->aggregations[$agg->getLabel()] = $agg;
        return $agg;
    }

    private function buildField($field_json, Elasticsearch_Model_Aggregation $agg = null): void
    {
        $field = new Elasticsearch_Model_Field(
            $field_json->name,
            $field_json->label,
            $field_json->type,
            $field_json->dublin_core,
            $agg
        );
        $this->fields[$field->getName()] = $field;
    }
}