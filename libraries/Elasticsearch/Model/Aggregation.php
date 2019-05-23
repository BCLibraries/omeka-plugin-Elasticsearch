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

    /**
     * @var int
     */
    private $size;

    public function __construct(string $name, string $label, string $field, int $size = null)
    {
        $this->name = $name;
        $this->label = $label;
        $this->field = $field;
        $this->size = $size;
    }

    public function toArray(): array
    {
        $params = [
            'terms' => [
                'field' => $this->field
            ]
        ];

        if (isset($this->size)) {
            $params['terms']['size'] = $this->size;
        }

        return $params;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getField(): string {
        return $this->field;
    }

}