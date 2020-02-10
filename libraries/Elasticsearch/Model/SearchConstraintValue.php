<?php

class Elasticsearch_Model_SearchConstraintValue
{
    /** @var string */
    private $value;

    /** @var bool */
    private $is_shown = true;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function getValue(): string
    {
        return $this->value;
    }

    public function isShown(): bool
    {
        return $this->is_shown;
    }

    public function hide(): void
    {
        $this->is_shown = false;
    }

    public function show(): void
    {
        $this->is_shown = true;
    }
}