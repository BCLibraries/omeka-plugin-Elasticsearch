<?php

class Elasticsearch_Model_Aggregation
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
    private $field;

    public function __construct(string $name, string $label, string $field)
    {
        $this->name = $name;
        $this->label = $label;
        $this->field = $field;
    }

    public function getParamArray(): array
    {
        return [
            'terms' => [
                'field' => $this->field
            ]
        ];
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getName(): string
    {
        return $this->name;
    }

}