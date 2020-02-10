<?php

class Elasticsearch_Model_SearchConstraint
{
    /** @var bool */
    private $is_search_param;

    /** @var bool */
    private $is_multi_val;

    /** @var string */
    private $key;

    /** @var Elasticsearch_Model_SearchConstraintValue[] */
    private $values = [];

    public function __construct(string $key)
    {
        $this->is_search_param = strpos($key, 'sort') !== 0;
        $this->is_multi_val = $this->is_search_param && $key !== 'q';
        $this->key = $key;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function addValue(string $value): void
    {
        $this->values[] = new Elasticsearch_Model_SearchConstraintValue($value);
    }

    /**
     * @return Elasticsearch_Model_SearchConstraintValue[]
     */
    public function getValues(): array
    {
        return array_filter($this->values, static function (Elasticsearch_Model_SearchConstraintValue $val) {
            return $val->isShown();
        });
    }

    public function isSearchParam(): bool
    {
        return $this->is_search_param;
    }

    public function isMultiParam(): bool
    {
        return $this->is_multi_val;
    }

    public function hasValues(): bool
    {
        return count($this->getValues()) > 0;
    }

    public function __toString(): string
    {
        $string_parts = [];

        if ($this->isMultiParam()) {
            $i = 0;
            foreach ($this->values as $val) {
                if ($val->isShown()) {
                    $string_parts[] = "{$this->key}[$i][or]=$val";
                    ++$i;
                }
            }
        } elseif ($val->isShown()) {
            $string_parts[] = "{$this->key}={$this->values[0]}";
        }

        return urlencode(implode('&', $string_parts));
    }

    public function getQueryStringMultiComponent(): array
    {
        $component = [];

        if (! $this->isMultiParam()) {
            throw new RuntimeException("Attempt to get multi-component on single component ({$this->key})");
        }

        if (! $this->hasValues()) {
            return null;
        }



        return $component;
    }
}