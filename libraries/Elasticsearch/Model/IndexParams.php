<?php

class Elasticsearch_Model_IndexParams
{
    /**
     * @param string $index
     * @return array
     */
    public static function getIndexParams(string $index): array
    {
        $params = [
            'index' => $index,
            'body' => [
                'settings' => new stdClass(), // emtpy settings object
                'mappings' => self::getMappings()
            ]
        ];
        return $params;
    }

    /**
     * This function defines the field mapping used in the elasticsearch index.
     *
     * The mapping defines fields common to all types of documents, as well
     * as fields specific to certain types of integrations (e.g. items, exhibits, etc).
     *
     * Integration-specific fields should be mentioned in the comments below.
     *
     * @return array
     */
    private static function getMappings()
    {
        $mappings = [
            'doc' => [
                'dynamic' => false,
                'properties' => array_merge(self::getBaseProperties(), self::getLocalProperties())
            ]
        ];

        return $mappings;
    }

    private static function getBaseProperties()
    {
        return [
            // Common Mappings
            'resulttype' => ['type' => 'keyword'],
            'title' => ['type' => 'text'],
            'description' => ['type' => 'text'],
            'text' => ['type' => 'text'],
            'featured' => ['type' => 'boolean'],
            'public' => ['type' => 'boolean'],
            'created' => ['type' => 'date'],
            'updated' => ['type' => 'date'],
            'tags' => ['type' => 'keyword'],
            'slug' => ['type' => 'keyword'],
            'url' => ['type' => 'keyword'],

            // Item-Specific
            'collection' => [
                'type' => 'text',
                'fields' => ['keyword' => ['type' => 'keyword']]
            ],
            'itemtype' => ['type' => 'keyword'],
            'element' => ['type' => 'object', 'dynamic' => true, 'properties' => new stdClass()],
            'elements' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'index' => false],
                    'displayName' => ['type' => 'keyword', 'index' => false],
                    'name' => ['type' => 'keyword', 'index' => false],
                    'text' => ['type' => 'text', 'index' => false],
                ]
            ],
            'files' => [
                'type' => 'object',
                'properties' => [
                    'id' => ['type' => 'integer', 'index' => false],
                    'title' => ['type' => 'keyword'],
                    'element' => ['type' => 'object', 'dynamic' => true, 'properties' => new stdClass()]
                ]
            ],

            // Exhibit-Specific
            'credits' => ['type' => 'text'],
            'exhibit' => [
                'type' => 'text',
                'fields' => ['keyword' => ['type' => 'keyword']]
            ],
            'blocks' => [
                'type' => 'object',
                'properties' => [
                    'text' => ['type' => 'text'],
                    'attachments' => ['type' => 'text']
                ]
            ],

            // Neatline-Specific
            'neatline' => ['type' => 'text'],
            'neatlineRecords' => ['type' => 'integer', 'index' => false]
        ];
    }

    private static function getLocalProperties(): array
    {
        $fields = Elasticsearch_Config::custom()->getFields();
        return array_map('Elasticsearch_Model_IndexParams::buildField', $fields);
    }

    private static function buildField(Elasticsearch_Model_Field $field)
    {
        return ['type' => $field->getType()];
    }
}