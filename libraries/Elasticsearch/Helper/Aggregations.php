<?php

class Elasticsearch_Helper_Aggregations
{
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