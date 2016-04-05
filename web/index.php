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
        http_response_code(404);
        echo "<h1>404 File not found</h1><h2>" . $uri . '</h2>';
        exit();
    }
}
$ext = pathinfo($filename, PATHINFO_EXTENSION);
switch ($ext) {
    case 'js':
        header("Content-Type: text/javascript");
        break;
    case 'css':
        header("Content-Type: text/css");
        break;
    case 'html':
        header("Content-Type: text/html");
        break;
    case 'png':
        header("Content-Type: image/png");
        break;
    case 'jpeg':
    case 'jpg':
        header("Content-Type: image/jpeg");
        break;
    default:
        header("Content-Type: application/octet-stream");
        break;
}
$content = file_get_contents($filename);
echo $content;
