<?php

require_once __DIR__ . '/../../../libraries/Elasticsearch/Helper/ConfigReader.php';

use PHPUnit\Framework\TestCase;

class Helper_ConfigReaderTest extends TestCase
{
    private const CONFIG_FILE = __DIR__ . '/../../elasticsearch.json';

    /**
     * @var Elasticsearch_Helper_ConfigReader;
     */
    private $reader;

    /**
     * @var Elasticsearch_Model_Aggregation
     */
    private $language_agg;

    /**
     * @var Elasticsearch_Model_Aggregation
     */
    private $model_agg;

    public function setUp(): void
    {
        parent::setUp();
        $this->language_agg = new Elasticsearch_Model_Aggregation('language', 'Language of the Letter', 'language');
        $this->model_agg = new Elasticsearch_Model_Aggregation('jmodel', 'Model', 'jmodel');

        $this->reader = new Elasticsearch_Helper_ConfigReader(self::CONFIG_FILE);
    }

    public function testGetAggregations(): void
    {
        $expected = [
            'Language of the Letter' => $this->language_agg,
            'Model' => $this->model_agg
        ];

        $this->assertEquals($expected, $this->reader->getAggregations());
    }

    public function testGetFields(): void
    {
        $lang_field = new Elasticsearch_Model_Field('language',
            'Language of the Letter',
            'keyword',
            'Language',
            $this->language_agg);
        $model_field = new Elasticsearch_Model_Field('jmodel',
            'Model', 'keyword',
            'Subject',
            $this->model_agg);
        $note_field = new Elasticsearch_Model_Field('note',
            'Notes',
            'text',
            'Abstract');
        $expected = [
            'language' => $lang_field,
            'jmodel' => $model_field,
            'note' => $note_field
        ];
        $this->assertEquals($expected, $this->reader->getFields());
    }
}
