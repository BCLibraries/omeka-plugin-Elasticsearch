<?php

require_once __DIR__ . '/SubQuery.php';

class Elasticsearch_Model_DateRangeQuery implements Elasticsearch_Model_SubQuery
{
    /**
     * @var array
     */
    private $query_array;

    /**
     * @var string
     */
    private $field_name;

    public static function build(string $field_name): Elasticsearch_Model_DateRangeQuery
    {
        return new Elasticsearch_Model_DateRangeQuery($field_name);
    }

    private function __construct(string $field_name)
    {
        $this->query_array = [];
        $this->field_name = $field_name;
    }

    public function greaterThan(string $date): Elasticsearch_Model_DateRangeQuery
    {
        $date = str_replace('"', '', $date);
        return $this->setRangeParameter('gt', $date);
    }

    public function greaterThanOrEqualTo(string $date): Elasticsearch_Model_DateRangeQuery
    {
        $date = str_replace('"', '', $date);
        return $this->setRangeParameter('gte', $date);
    }

    public function lessThan(string $date): Elasticsearch_Model_DateRangeQuery
    {
        $date = str_replace('"', '', $date);
        return $this->setRangeParameter('lt', $date);
    }

    public function lessThanOrEqualTo(string $date): Elasticsearch_Model_DateRangeQuery
    {
        $date = str_replace('"', '', $date);
        return $this->setRangeParameter('lte', $date);
    }

    public function toArray(): array
    {
        return [
            'range' => [
                $this->field_name => $this->query_array
            ]
        ];
    }

    private function setRangeParameter(string $operator, string $date): Elasticsearch_Model_DateRangeQuery
    {
        // Only accept years for now.
        if (!preg_match('/^\d\d\d\d$/', $date)) {
            throw new Elasticsearch_Exception_BadQueryException('Date queries must be years in the format yyyy');
        }
        $this->query_array[$operator] = $date;
        return $this;
    }
}