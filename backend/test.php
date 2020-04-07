<?php
require __DIR__ . '/vendor/autoload.php';

use Games\Test\ChessServerTest;
use Games\Test\GoatServerTest;

$chessServer = new GoatServerTest;
$chessServer->start();
