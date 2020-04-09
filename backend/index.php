<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\App;
use Games\Chess\ChessServer;
use Games\Card\Goat\GoatServer;

[, $localIp, $port] = $argv;
$publicIp = $argv[3] ??  $localIp;
echo "Bind server to address $localIp:$port\n";
$app = new App($localIp, $port, $publicIp);
$app->route('/chess', new ChessServer, ['*']);
$app->route('/goat', new GoatServer, ['*']);
$app->run();
