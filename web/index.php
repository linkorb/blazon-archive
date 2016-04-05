<?php

use Blazon\Blazon;

$loader = __DIR__ . '/../vendor/autoload.php';

if (!file_exists($loader)) {
    $loader = __DIR__ . '/../../../autoload.php';
}

if (!file_exists($loader)) {
    die(
        'You must set up the project dependencies, run the following commands:' . PHP_EOL .
        'curl -s http://getcomposer.org/installer | php' . PHP_EOL .
        'php composer.phar install' . PHP_EOL
    );
}

$autoLoader = require($loader);

$filename = getenv('BLAZON_FILE');
if (!$filename) {
    throw new RuntimeException("Environment variable BLAZON_FILE not set (correctly)");
}


$blazon = new Blazon($filename, null, null);
$blazon->run();

//print_r($_SERVER);

$uri = $_SERVER['REQUEST_URI'];
if ($uri=='/') {
    $uri = '/index';
}
$filename = $blazon->getDest() . $uri;
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
