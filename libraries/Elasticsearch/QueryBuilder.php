<?php

require_once __DIR__ . '/Model/SubQuery.php';
require_once __DIR__ . '/Model/ShouldQuery.php';
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
        $size = 9999;
        $page = $query_params['page'] ?? 1;
        $offset = ($page - 1) * $size;

        if (!isset($query_params['sort'])) {
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
        return ($field === 'date_range') ? $this->dateQuery($value) : $this->queryString($field, $value);
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
        return strpos($field, 'min_') === 0 && strpos($field, 'max_') === 0;
    }

    /**
     * Build a date query from a string
     *
     * The string should be a range of the format "1843-1844". If there is only one value, the entire search string
     * will be used as a minimum and maximum.
     *
     * @param $value
     * @return Elasticsearch_Model_RangeQuery
     */
    private function dateQuery($value): Elasticsearch_Model_ShouldQuery
    {
        $shoulds = [];

        foreach ($value as $date_range) {
            $parts = explode(' – ', $date_range['or']);

            $should = Elasticsearch_Model_RangeQuery::build('date');
            if ($parts[0] !== '_') {
                $should->greaterThanOrEqualTo($parts[0]);
            }

            if ($parts[1] !== '_') {
                $should->lessThanOrEqualTo($parts[1]);
            }

            $shoulds[] = $should;
        }

        return new Elasticsearch_Model_ShouldQuery($shoulds);
    }

    /**
     * Build a query string for a subquery
     *
     * Handles both keyword ("q") and non-keyword subqueries
     *
     * @param $field
     * @param $value
     * @return Elasticsearch_Model_QueryStringQuery
     */
    private function queryString($field, $value): Elasticsearch_Model_SubQuery
    {
        // If the value is an array, it is a non-keyword query
        if (is_array($value)) {
            return $this->buildNonKeywordQueryString($field, $value);
        }

        // Keyword query with no value. Return an empty query.
        if (!$value) {
            return Elasticsearch_Model_MatchAllQuery::build();
        }

        // Regular keyword query.
        return Elasticsearch_Model_QueryStringQuery::build($value);
    }

    /**
     * Builds a query string for a non-keyword field
     *
     * To accommodate multiple values, non-keyword fields are passed in $_GET as an array
     * of arrays, e.g.:
     *
     *    'from' => [
     *                [
     *                   'or' => 'Turin'
     *                ],
     *                [
     *                    'or' => 'Genoa'
     *                ],
     *             ]
     *
     * This function reduces such an array to 'Turin OR Genoa'.
     *
     * @param $field
     * @param $value_list
     * @return Elasticsearch_Model_QueryStringQuery
     */
    private function buildNonKeywordQueryString($field, $value_list): Elasticsearch_Model_QueryStringQuery
    {
        // For now, remove anything without an explicit "OR" indicator.
        $value_list = array_filter($value_list, function ($value) {
            return isset($value['or']);
        });

        // Join all the values into a query string
        $value_string = array_reduce($value_list, function ($value_string, $value) {
            return $value_string ? "$value_string OR {$value['or']}" : (string)$value['or'];
        });

        $query = Elasticsearch_Model_QueryStringQuery::build($value_string);
        $query->defaultField($field);
        return $query;
    }
}