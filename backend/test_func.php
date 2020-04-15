<?php
require __DIR__ . '/vendor/autoload.php';
ini_set('xdebug.halt_level', E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE);
ini_set('xdebug.max_nesting_level', 16384);
set_error_handler(fn ($errno, $errstr, $errfile, $errline) => file_put_contents('err.log', "$errno, $errstr, $errfile, $errline"));

use Games\Test\GoatServerTest;

$test = new GoatServerTest;
$test->start();
