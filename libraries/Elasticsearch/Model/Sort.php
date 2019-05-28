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

    public function url(): string
    {
        $query_data = $_GET;
        unset($query_data['sort'], $query_data['sort_dir'], $query_data['page']);
        $query_data['sort'] = $this->field;
        if ($this->direction !== 'asc') {
            $query_data['sort_dir'] = $this->direction;
        }
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

    public function toArray(): array
    {
        return [$this->field => $this->direction];
    }
}