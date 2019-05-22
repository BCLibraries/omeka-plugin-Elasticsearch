<?php

require_once __DIR__ . '/Model/SubQuery.php';
require_once __DIR__ . '/Model/MatchAllQuery.php';
require_once __DIR__ . '/Model/QueryStringQuery.php';
require_once __DIR__ . '/Model/DateRangeQuery.php';
require_once __DIR__ . '/Model/TermQuery.php';
require_once __DIR__ . '/Model/Query.php';

class Elasticsearch_QueryBuilder
{
    public function build(array $query_params, array $aggregations): Elasticsearch_Model_Query
    {
        $facets = array_filter($query_params, [$this, 'isFacet'], ARRAY_FILTER_USE_KEY);
        $search_terms = array_diff_assoc($query_params, $facets);

        $empty_subquery = Elasticsearch_Model_MatchAllQuery::build();

        $subqueries = empty($search_terms) ? $empty_subquery : array_map(
            [$this, 'buildSubQuery'],
            array_keys($search_terms),
            $search_terms);
        $filters = array_map([$this, 'buildFilter'], array_keys($facets), $facets);

        $agglist = new Elasticsearch_Model_AggregationList($aggregations);

        return new Elasticsearch_Model_Query($subqueries, $filters, $agglist);
    }

    private function buildSubQuery($field, $value): Elasticsearch_Model_SubQuery
    {
        return ($field === 'date') ? $this->dateQuery($value) : $this->queryString($field, $value);
    }

    private function buildFilter($facet_name, $value): Elasticsearch_Model_TermQuery
    {
        return Elasticsearch_Model_TermQuery::build($facet_name, $value);
    }

    private function isFacet($field): bool
    {
        return strpos($field, 'facet_') === 0;
    }

    /**
     * @param $value
     * @return Elasticsearch_Model_DateRangeQuery
     */
    private function dateQuery($value): Elasticsearch_Model_DateRangeQuery
    {
        return Elasticsearch_Model_DateRangeQuery::build('date')
            ->greaterThanOrEqualTo($value[0])
            ->lessThanOrEqualTo($value[1]);
    }

    /**
     * @param $field
     * @param $value
     * @return Elasticsearch_Model_QueryStringQuery
     */
    private function queryString($field, $value): Elasticsearch_Model_QueryStringQuery
    {
        $query = Elasticsearch_Model_QueryStringQuery::build($value);

        if ($field !== 'any') {
            $query->defaultField($field);
        }

        return $query;
    }
}