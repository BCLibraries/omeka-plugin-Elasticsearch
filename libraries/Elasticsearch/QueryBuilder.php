<?php

require_once __DIR__ . '/Model/SubQuery.php';
require_once __DIR__ . '/Model/MatchAllQuery.php';
require_once __DIR__ . '/Model/QueryStringQuery.php';
require_once __DIR__ . '/Model/DateRangeQuery.php';
require_once __DIR__ . '/Model/TermQuery.php';
require_once __DIR__ . '/Model/Query.php';

class Elasticsearch_QueryBuilder
{

    /**
     * Build a query
     *
     * @param array $query_params values set in query string
     * @param Elasticsearch_Model_Aggregation[] $aggregations
     * @return Elasticsearch_Model_Query
     * @throws Elasticsearch_Exception_BadQueryException
     */
    public function build(array $query_params, array $aggregations): Elasticsearch_Model_Query
    {
        $size = 15;
        $page = $query_params['page'] ?? 1;
        $offset = ($page - 1) * $size;

        if (! isset($query_params['sort'])) {
            $query_params['sort'] = 'date';
            $query_params['sort_dir'] = 'asc';
        }
        $sort = $query_params['sort'] ?? null;
        $sort_dir = $query_params['sort_dir'] ?? 'asc';

        unset($query_params['page'], $query_params['sort'], $query_params['sort_dir']);

        $facets = array_filter($query_params, [$this, 'isFacet'], ARRAY_FILTER_USE_KEY);
        $search_terms = array_diff_assoc($query_params, $facets);

        $empty_subquery = Elasticsearch_Model_MatchAllQuery::build();

        $subqueries = empty($search_terms) ? [$empty_subquery] : array_map(
            [$this, 'buildSubQuery'],
            array_keys($search_terms),
            $search_terms);
        $filters = array_map([$this, 'buildFilter'], array_keys($facets), $facets);

        $agglist = new Elasticsearch_Model_AggregationList($aggregations);

        $query = Elasticsearch_Model_Query::build($subqueries, $filters, $agglist)
            ->offset($offset)
            ->limit($size);

        if ($sort) {
            $query->sort(new Elasticsearch_Model_Sort($sort, $sort_dir));
        }

        return $query;
    }

    private function buildSubQuery($field, $value): Elasticsearch_Model_SubQuery
    {
        return ($field === 'year') ? $this->dateQuery($value) : $this->queryString($field, $value);
    }

    private function buildFilter($facet_name, $value): Elasticsearch_Model_TermQuery
    {
        return Elasticsearch_Model_TermQuery::build($facet_name, $value);
    }

    private function isFacet($field): bool
    {
        return strpos($field, 'facet_') === 0;
    }

    private function isRange($field): bool
    {
        return strpos($field, 'min_') === 0 && strpos($field, 'max_')  === 0;
    }

    /**
     * @param $value
     * @return Elasticsearch_Model_DateRangeQuery
     */
    private function dateQuery($value): Elasticsearch_Model_RangeQuery
    {
        $parts = explode('-', $value);

        $query = Elasticsearch_Model_RangeQuery::build('year');
        if ($parts[0] !== '_') {
            $query->greaterThanOrEqualTo($parts[0]);
        }

        if ($parts[1] !== '_') {
            $query->lessThanOrEqualTo($parts[1]);
        }

        return $query;
    }

    /**
     * @param $field
     * @param $value
     * @return Elasticsearch_Model_QueryStringQuery
     */
    private function queryString($field, $value): Elasticsearch_Model_SubQuery
    {
        $query = Elasticsearch_Model_QueryStringQuery::build($value);

        if ($field !== 'q') {
            $query->defaultField($field);
        } else {
            if (!$value) {
                $query = Elasticsearch_Model_MatchAllQuery::build();
            }
        }

        return $query;
    }
}