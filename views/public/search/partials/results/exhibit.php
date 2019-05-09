<?php $result_img = record_image($record, 'thumbnail', array('class' => 'elasticsearch-result-image')); ?>
<?php $description = strip_tags($hit['_source']['description'], '<p><br><i><b><em>'); ?>
<?php if ($result_img): ?>
    <a href="<?= $url ?>"><?= $result_img ?></a>
<?php endif; ?>

<ul>
    <li title="resulttype"><b>Result Type:</b> <?php echo $hit['_source']['resulttype']; ?></li>
    <li title="description"><b>Description:</b>
        <?= Elasticsearch_Utils::truncateText($description, $maxTextLength) ?>
    </li>
    <?php if (isset($hit['_source']['tags']) && count($hit['_source']['tags']) > 0): ?>
        <li title="tags"><b>Tags:</b> <?= implode(', ', $hit['_source']['tags']) ?></li>
    <?php endif; ?>
    <li title="created"><b>Record Created: </b> <?= Elasticsearch_Utils::formatDate($hit['_source']['created']) ?></li>
    <li title="updated"><b>Record Updated: </b> <?= Elasticsearch_Utils::formatDate($hit['_source']['updated']) ?></li>
</ul>

