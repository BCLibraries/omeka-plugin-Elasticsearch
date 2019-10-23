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

    /**
     * @return Elasticsearch_Model_Aggregation[]
     */
    public static function getAllAggregations(): array
    {
        $aggs = [];
        foreach (Elasticsearch_Config::custom()->getAggregations() as $aggregation) {
            $aggs[$aggregation->getName()] = $aggregation;
        }
        return $aggs;
    }

    /**
     * @return Elasticsearch_Model_FacetBucket[]
     */
    public static function getAppliedFacets(): array
    {
        $applied_facets = [];

        foreach (self::getAllAggregations() as $aggregation) {
            if (isset($_GET[$aggregation->getField()])) {
                $applied_facets[$aggregation->getField()] = new Elasticsearch_Model_FacetBucket
                (
                    $aggregation->getField(),
                    htmlspecialchars($aggregation->getLabel()),
                    htmlspecialchars(Elasticsearch_Utils::facetVal2Str($_GET[$aggregation->getField()])),
                    htmlspecialchars(Elasticsearch_Utils::facetVal2Str($_GET[$aggregation->getField()]))
                );
            }
        }
        return $applied_facets;
    }

    /**
     * @param array $json_aggregations
     * @return Elasticsearch_Model_Facet[]
     */
    public static function getResultFacets(array $json_aggregations): array
    {
        $all_aggregations = self::getAllAggregations();

        $facets = [];

        foreach ($all_aggregations as $aggregation) {
            $facet = new Elasticsearch_Model_Facet(
                $aggregation->getLabel(),
                $aggregation->getName(),
                $aggregation->getField(),
                []
            );

            $result_agg = $json_aggregations[$aggregation->getName()];

            foreach ($result_agg['buckets'] as $bucket) {
                $facet->buckets[] = new Elasticsearch_Model_FacetBucket(
                    $aggregation->getField(),
                    $aggregation->getLabel(),
                    $bucket['key'],
                    $bucket['key_as_string'] ?? $bucket['key'],
                    $bucket['doc_count']
                );
            }

            $facet->setUrl($result_agg['url']);

            if (count($facet->buckets) > 0) {
                $facets[] = $facet;
            }
        }

        return $facets;
    }
}