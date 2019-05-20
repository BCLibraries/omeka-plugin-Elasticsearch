<?php

class Elasticsearch_Model_MatchAllQuery implements Elasticsearch_Model_SubQuery
{
    public static function build(): Elasticsearch_Model_MatchAllQuery
    {
        return new Elasticsearch_Model_MatchAllQuery();
    }

    private function __construct()
    {

    }

    public function toArray(): array
    {
        return ['match_all' => new \stdClass()];
    }
}