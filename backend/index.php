<?php
require __DIR__ . '/vendor/autoload.php';

use Ratchet\App;
use Games\Chess\ChessServer;
use Games\Card\Goat\GoatServer;
use function Games\Util\Misc\getArg;
use function Games\Util\String\fixedExplode;

[, $localIp, $port] = $argv;
$publicIp = getArg(3, $localIp);
$initialTeamScore = fixedExplode('-', getArg(4, ''));

echo "Bind server to address $publicIp:$port\n";
$app = new App($localIp, $port, $publicIp);
$app->route('/chess', new ChessServer);
$app->route('/goat', new GoatServer($initialTeamScore));
$app->run();
