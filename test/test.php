<?php

use OsfParser\Parser;

require dirname(__DIR__) . '/vendor/autoload.php';

$shownotesTest = file_get_contents(__DIR__ . '/test-1.txt');
$parser = new Parser();
$parser->parse($shownotesTest);
