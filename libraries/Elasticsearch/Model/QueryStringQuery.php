<?php

require_once __DIR__.'/../Exception/BadQueryException.php';

class Elasticsearch_Model_QueryStringQuery implements Elasticsearch_Model_SubQuery
{
    /**
     * @var array
     */
    private $query_array;

    public static function build(string $query): Elasticsearch_Model_QueryStringQuery
    {
        return new Elasticsearch_Model_QueryStringQuery($query);
    }

    private function __construct(string $query)
    {
        $this->query_array = [
            'query' => $query
        ];
    }

    public function defaultField(string $field): Elasticsearch_Model_QueryStringQuery
    {
        $this->query_array['default_field'] = $field;
        return $this;
    }

    public function defaultOperator(string $operator): Elasticsearch_Model_QueryStringQuery
    {
        if (!in_array($operator, ['AND', 'OR'], true)) {
            throw new Elasticsearch_Exception_BadQueryException("$operator is not a valid boolean operator (use 'AND' or 'OR')");
        }
        $this->query_array['default_operator'] = $operator;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'query_string' => $this->query_array
        ];
    }
}