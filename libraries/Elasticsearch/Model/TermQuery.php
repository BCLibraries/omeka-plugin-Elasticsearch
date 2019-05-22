<?php

class Elasticsearch_Model_TermQuery implements Elasticsearch_Model_SubQuery
{
    private $field_name;
    private $value;

    public static function build(string $field_name, $value): \Elasticsearch_Model_TermQuery
    {
        return new Elasticsearch_Model_TermQuery($field_name, $value);
    }

    private function __construct(string $field_name, $value)
    {
        $this->field_name = $field_name;
        $this->value = $value;
    }

    public function toArray():array
    {
        return [
            'term' => [
                $this->field_name => $this->value
            ]
        ];
    }
}