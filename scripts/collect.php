<?php
require_once __DIR__ . '/../src/bootstrap.php';
require_once __DIR__ . '/../src/Collector.php';

$collector = new Collector();
$items = [
    ['source_name' => 'PIB', 'title' => 'Sample scheme update', 'content' => 'Budget approved for road expansion', 'leader_name' => 'Sample Leader', 'state' => 'Maharashtra'],
];
$result = $collector->run($items);
echo 'Processed: ' . $result['processed'] . PHP_EOL;
