<?php

class Elasticsearch_Model_Aggregations
{
    /**
     * Returns aggregations that should be returned for every search query.
     *
     * @return array
     */
    public static function getAggregationsParams(): array
    {
        $agg_params = [];
        $aggregations = Elasticsearch_Config::custom()->getAggregations();
        foreach ($aggregations as $aggregation) {
            $agg_params[$aggregation->getName()] = $aggregation->getParamArray();
        }
        return $agg_params;
    }

    /**
     * Returns display labels for aggregation keys (e.g. "Result Type" for "resulttype").
     *
     * @return array
     */
    public static function getAggregationLabels(): array
    {
        $labels = [];
        foreach (Elasticsearch_Config::custom()->getAggregations() as $aggregation) {
            $labels[$aggregation->getName()] = $aggregation->getLabel();
        }
        return $labels;
    }
}