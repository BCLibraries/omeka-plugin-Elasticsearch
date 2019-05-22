<?php

require_once __DIR__ . '/../../../libraries/Elasticsearch/Model/TermQuery.php';

use PHPUnit\Framework\TestCase;

class Model_TermQueryTest extends TestCase
{
    public function testBuildGeneratesTermQuery()
    {
        $expected = [
            'term' => ['foo' => 'bar']
        ];
        $this->assertEquals($expected, Elasticsearch_Model_TermQuery::build('foo', 'bar')->toArray());
    }
}
