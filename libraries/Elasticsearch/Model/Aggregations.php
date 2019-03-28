<?php

class Elasticsearch_Model_Aggregations
{
    /**
     * Returns aggregations that should be returned for every search query.
     *
     * @return array
     */
    public static function getAggregations()
    {
        $aggregations = [
            'recipients' => [
                'terms' => [
                    'field' => 'recipient'
                ]
            ],
            'senders' =>  [
                'terms' => [
                    'field' => 'sender'
                ]
            ],
            'tos' =>  [
                'terms' => [
                    'field' => 'to'
                ]
            ],
        ];
        return $aggregations;
    }

    /**
     * Returns display labels for aggregation keys (e.g. "Result Type" for "resulttype").
     *
     * @return array
     */
    public static function getAggregationLabels()
    {
        return [
            'recipients' => 'Recipients',
            'tos' => 'To',
            'senders' => 'Senders'
        ];
    }
}