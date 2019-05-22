<?php

class Elasticsearch_Model_Sort
{
    /**
     * @var string
     */
    private $field;

    /**
     * @var string
     */
    private $direction;

    public function __construct(string $field, string $direction = 'asc')
    {
        $this->field = $field;
        if ($direction !== 'asc' && $direction !== 'desc') {
            throw new Elasticsearch_Exception_BadQueryException('Valid sort directions are "asc" and "desc"');
        }
        $this->direction = $direction;
    }

    public function toArray(): array
    {
        return [$this->field => $this->direction];
    }
}