<?php
require __DIR__ . '/vendor/autoload.php';

use Games\Test\ChessTestServer;

$chessServer = new ChessTestServer;
$chessServer->start();
