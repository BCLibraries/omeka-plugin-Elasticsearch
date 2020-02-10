<?php

class Elasticsearch_Integration_Items extends Elasticsearch_Integration_BaseIntegration
{
    protected $_hooks = array(
        'after_save_item',
        'after_save_file',
        'after_delete_item',
        'after_delete_file',
    );

    /**
     * Hook for when an item is being saved.
     * Indexes item in the elasticsearch cluster.
     *
     * @param array $args
     */
    public function hookAfterSaveItem($args)
    {
        $this->_log("hookAfterSaveItem: {$args['record']->id}");
        $this->indexItem($args['record']);
    }

    /**
     * Hook for when an item is being deleted.
     * Removes item from the elasticsearch index.
     *
     * @param array $args
     */
    public function hookAfterDeleteItem($args)
    {
        $this->_log("deleting item from index: {$args['record']->id}");
        $this->deleteItem($args['record']);
    }

    /**
     * Hook for when a file is being saved.
     * Update the indexed item document.
     *
     * @param array $args
     */
    public function hookAfterSaveFile($args)
    {
        $this->_log("hookAfterSaveFile: {$args['record']->id}");
        $file = $args['record'];
        if ($item = $file->getItem()) {
            $this->indexItem($item);
        }
    }

    /**
     * Hook for when a file is being deleted.
     * Update the indexed item document.
     *
     * @param array $args
     */
    public function hookAfterDeleteFile($args)
    {
        $this->_log("hookAfterDeleteFile: {$args['record']->id}");
        $file = $args['record'];
        if ($item = $file->getItem()) {
            $this->indexItem($item);
        }
    }

    /**
     * Indexes a single Item record.
     *
     * @param $item
     * @return array
     */
    public function indexItem($item)
    {
        $doc = $this->getItemDocument($item);
        return $doc->index();
    }

    /**
     * Deletes an item from the index.
     *
     * @param $item
     */
    public function deleteItem($item)
    {
        $doc = new Elasticsearch_Document($this->_docIndex, "item_{$item->id}");
        return $doc->delete();
    }

    /**
     * Returns an item as a document.
     *
     * @param $item
     * @return Elasticsearch_Document
     */
    public function getItemDocument(Item $item)
    {
        _log("Logging item {$item->id}");
        $doc = new Elasticsearch_Document($this->_docIndex, "item_{$item->id}");
        $fields = [
            'resulttype' => 'Item',
            'model' => 'Item',
            'modelid' => $item->id,
            'featured' => (bool)$item->featured,
            'public' => (bool)$item->public,
            'created' => $this->_getDate($item->added),
            'updated' => $this->_getDate($item->modified),
            'title' => metadata($item, array('Dublin Core', 'Title'))
        ];

        $doc->setFields(array_merge($fields, self::getLocalFields($item)));

        // collection:
        if ($collection = $item->getCollection()) {
            $doc->setField('collection', metadata($collection, array('Dublin Core', 'Title')));
        }

        // item type:
        if ($itemType = $item->getItemType()) {
            $doc->setField('itemtype', $itemType->name);
        }

        // elements:
        $itemElementTexts = $this->_getElementTexts($item);
        $doc->setField('elements', $itemElementTexts['elements']);
        $doc->setField('element', $itemElementTexts['element']);

        // tags:
        $tags = [];
        foreach ($item->getTags() as $tag) {
            $tags[] = $tag->name;
        }
        $doc->setField('tags', $tags);

        // files:
        $files = [];
        if ($itemFiles = $item->getFiles()) {
            foreach ($itemFiles as $itemFile) {
                $fileElementTexts = $this->_getElementTexts($itemFile);
                $files[] = [
                    'id' => $itemFile->id,
                    'title' => $itemFile->getProperty('display_title'),
                    'element' => $fileElementTexts['data']
                ];
            }
        }
        $doc->setField('files', $files);

        return $doc;
    }

    /**
     * Get array of documents to index.
     *
     * @return array
     */
    public function getItemDocuments()
    {
        $docs = [];
        $items = $this->_fetchObjects('Item');
        foreach ($items as $item) {
            $docs[] = $this->getItemDocument($item);
        }
        return $docs;
    }

    /**
     * Index all items.
     */
    public function indexAll()
    {
        $docs = $this->getItemDocuments();
        if (isset($docs)) {
            $this->_log('indexAll items: ' . count($docs));
            Elasticsearch_Document::bulkIndex($docs);
        }
    }

    /**
     * Deletes all items from the index.
     */
    public function deleteAll()
    {
        $this->_deleteByQueryModel('Item');
    }

    /**
     * Helper function to extract element texts from a record.
     *
     * @param $record
     * @return array
     */
    protected function _getElementTexts($record, $options = array())
    {
        _log("Logging {$record->id}");
        $opt_normalize = isset($options['normalize']) ? (bool)$options['normalize'] : true;

        // Retrieve all of the element texts (each element could have several texts - multi-valued)
        $elementById = [];
        $elementOrderById = [];
        try {
            foreach ($record->getAllElementTexts() as $elementText) {
                $element = $record->getElementById($elementText->element_id);
                if (!isset($elementById[$element->id])) {
                    if ($opt_normalize) {
                        $nameNormalized = strtolower(preg_replace('/[^a-zA-Z0-9-_]/', '', $element->name));
                    } else {
                        $nameNormalized = $element->name;
                    }
                    $elementById[$element->id] = [
                        'id' => $element->id,
                        'displayName' => $element->name,
                        'name' => $nameNormalized,
                        'text' => []
                    ];
                    $elementOrderById[] = $element->id;
                    if ($nameNormalized === 'datesubmitted') {

                        $potential_date = $elementText->text;
                        $potential_date = str_replace('-00', '-01', $potential_date);

                        $new_date = DateTime::createFromFormat('Y-m-d', trim($potential_date));

                        if ($new_date === false) {
                            $new_date = DateTime::createFromFormat('Y-m-d', '2000-01-01');
                        }

                        $elementText->text = $new_date->format('Y-m-d');

                    }
                }
                if ($elementText->text === 'yes' || $elementText->text === 'no') {
                    $this->_log("{$record->id}:::{$element->id} has value '{$elementText->text}'");
                } elseif ($elementText->text === 'Yes' || $elementText->text === 'No') {
                    $this->_log("{$record->id}:::{$element->id} has value '{$elementText->text}'");
                }
                $elementById[$element->id]['text'][] = trim($elementText->text);
            }

        } catch (Omeka_Record_Exception $e) {
            $this->_log("Error loading elements for record {$record->id}. Error: " . $e->getMessage(), Zend_Log::WARN);
        }

        // Divide the element data into an ordered array and a mapping object
        $elements = [];
        $element = [];
        foreach ($elementOrderById as $id) {
            $data = $elementById[$id];
            $elements[] = $data;
            $element[$data['name']] = $data['text'];
        }

        _log("...logged {$record->id}");
        return array('elements' => $elements, 'element' => $element);
    }

    private static function getLocalFields($item): array
    {
        $fields = [];
        foreach (Elasticsearch_Config::custom()->getFields() as $field) {
            $origin = $field->getOrigin();

            if ($origin === 'Collection' && $collection = $item->getCollection()) {
                $source = $item->getCollection();
                $origin = 'Title';
            } else {
                $source = $item;
            }

            $values = self::getMetadataValue($source, $origin);

            if ($field->getRegex()) {
                $regex = $field->getRegex();
                $values = array_map(function ($value) use ($regex) {
                    $matches = [];
                    preg_match($regex, $value, $matches);
                    $value = $matches[0];
                    return $value;
                }, $values);
            }

            if ($field->getType() === 'integer') {
                $values = array_map('intval', $values);
            }

            $fields[$field->getName()] = $values;
        }
        return $fields;
    }

    private static function getMetadataValue($item, string $field): array
    {
        $values = [];
        if ($field === 'Collection') {
            return $item->getDisplayTitle(); // @todo make this work!
        }

        if ($field === 'Has Version') {
            $number_text = metadata($item, ['Dublin Core', 'Has Version'], ['all' => true])[0];
            return [(int)preg_replace('/^.+ +/', '', $number_text)];
        }

        if ($field === 'Call Number') {
            $archive = self::getField($item, 'Identifier')[0];
            $folder = self::getField($item, 'Has Format')[0];
            $number = self::getField($item, 'Has Version')[0];
            return ["$archive, $folder, $number"];
        }

        foreach (metadata($item, ['Dublin Core', $field], ['all' => true]) as $metadatum) {
            $values[] = trim(strip_tags($metadatum));
        }
        return $values;
    }

    protected static function getField($item, string $field): array
    {
        return metadata($item, ['Dublin Core', $field], ['no_filter' => true, 'all' => true]);
    }
}