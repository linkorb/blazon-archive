<?php

use Blazon\Blazon;

require_once(__DIR__ . '/../vendor/autoload.php');

$src = getenv('BLAZON_SRC');
$dest = getenv('BLAZON_DEST');

if (!$src || !$dest) {
    throw new RuntimeException("Environment variable BLAZON_SRC or BLAZON_DEST not set (correctly)");
}


$blazon = new Blazon($src, $dest);
$blazon->run();

//print_r($_SERVER);

$uri = $_SERVER['REQUEST_URI'];
if ($uri=='/') {
    $uri = '/index';
}
$filename = $dest . $uri;
if (!file_exists($filename)) {
    $filename .= '.html';
    if (!file_exists($filename)) {
        echo "File not found: " . $uri;
        exit();
    }
}
$content = file_get_contents($filename);
echo $content;
