<?php

require_once __DIR__ . '/../../../libraries/Elasticsearch/Model/MatchAllQuery.php';

use PHPUnit\Framework\TestCase;

class Model_MatchAllQueryTest extends TestCase
{
    public function testBuildGeneratesMatchAllQuery(): void
    {
        $expected = [
            'match_all' => new stdClass()
        ];
        $match_all = Elasticsearch_Model_MatchAllQuery::build();
        $this->assertEquals($expected, $match_all->toArray());
    }
}
