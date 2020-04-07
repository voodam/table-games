<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\App;
use Games\Chess\ChessServer;
use Games\Card\Goat\GoatServer;

[, $localIp, $port] = $argv;
echo "Bind server to address $localIp:$port\n";
$app = new App($localIp, $port, $localIp);
$app->route('/chess', new ChessServer, ['*']);
$app->route('/goat', new GoatServer, ['*']);
$app->run();
