<?php
require __DIR__ . '/vendor/autoload.php';
ini_set('xdebug.halt_level', E_WARNING | E_NOTICE | E_USER_WARNING | E_USER_NOTICE);
ini_set('xdebug.max_nesting_level', 16384);

use Games\Test\GoatServerTest;

$chessServer = new GoatServerTest;
$chessServer->start();
