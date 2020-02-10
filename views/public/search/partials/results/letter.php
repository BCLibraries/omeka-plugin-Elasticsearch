<?php

$elements = [];
if (isset($hit['elements'])) {
    $elements = array_reduce($hit['elements'], function ($hash, $element) {
        $name = $element['name'];
        $hash[$name] = $element['text'];
        return $hash;
    }, $elements);
}

$recipients = isset($elements['audience']) ? implode(', ', $elements['audience']) : null;
$destinations = isset($elements['publisher']) ? implode(', ', $elements['publisher']) : null;

$recipients_heading = count($elements['audience']) > 1 ? 'Recipients' : 'Recipient';
$destinations_heading = count($elements['publisher']) > 1 ? 'Destinations' : 'Destination';

?>
<ul>
    <li title="recipient"><b><?= $recipients_heading ?>:</b> <?= $recipients ?></li>

    <li title="destination"><b><?= $destinations_heading ?>:</b> <?= $destinations ?></li>
</ul>

