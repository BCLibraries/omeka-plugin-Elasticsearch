<?php

class Elasticsearch_Model_FacetBucket
{

    /**
     * @var string
     */
    public $field;

    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $count;

    /**
     * @var string
     */
    public $display_value;

    // @todo Add testing for facet buckets

    public function __construct(
        string $field,
        string $label,
        string $value,
        string $display_value,
        string $count = null
    ) {
        $this->field = $field;
        $this->label = $label;
        $this->value = $value;
        $this->count = $count;
        $this->display_value = $display_value;
    }

    public function url(): string
    {
        $query_data = $_GET;
        unset($query_data[$this->field], $query_data['page']);
        $query_data[$this->field] = $this->value;
        return $this->urlWithoutQuery() . '?' . http_build_query($query_data);
    }

    public function removeUrl(): string
    {
        $query_data = $_GET;
        unset($query_data[$this->field], $query_data['page']);
        return $this->urlWithoutQuery() . '?' . http_build_query($query_data);
    }

    /**
     * @return string
     */
    private function urlWithoutQuery(): string
    {
        $uri_without_query_string = "//$_SERVER[HTTP_HOST]" . strtok($_SERVER['REQUEST_URI'], '?');
        return $uri_without_query_string;
    }
}