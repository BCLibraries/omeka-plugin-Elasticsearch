<?php

require __DIR__.'/../../../libraries/Elasticsearch/Model/Sort.php';

use PHPUnit\Framework\TestCase;

class Model_SortTest extends TestCase
{
    public function testSortBuildsCorrectly(): void
    {
        $expected = ['foo' => 'desc'];
        $sort = new Elasticsearch_Model_Sort('foo', 'desc');
        $this->assertEquals($expected, $sort->toArray());
    }

    public function testDefaultOrderIsAsc(): void
    {
        $expected = ['foo' => 'asc'];
        $sort = new Elasticsearch_Model_Sort('foo');
        $this->assertEquals($expected, $sort->toArray());
    }

    public function testThrowsExceptionOnBadOrder(): void
    {
        $this->expectException(Elasticsearch_Exception_BadQueryException::class);
        $sort = new Elasticsearch_Model_Sort('foo', 'up');
    }
}
