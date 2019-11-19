<?php


class Elasticsearch_Model_ShouldQuery implements \Elasticsearch_Model_SubQuery
{
    /**
     * @var array
     */
    public $query_array;

    /**
     * Elasticsearch_Model_ShouldQuery constructor.
     * @param array $components
     */
    public function __construct(array $components)
    {
        $this->query_array = [
            'bool' => [
                'should' => array_map(static function (\Elasticsearch_Model_SubQuery $query) {
                    return $query->toArray();
                }, $components),
                'minimum_should_match' => 1
            ]
        ];
    }

    public function toArray(): array
    {
        return $this->query_array;
    }
}