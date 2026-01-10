<?php
header('Content-Type: application/json');
$tsFile = __DIR__ . '/cache/categories_ts.txt';
$ts = 0;
if (file_exists($tsFile)) {
    $ts = (int) @filemtime($tsFile);
}
echo json_encode(['ts' => $ts]);
