<?php

namespace Games\Test;

use Games\GameServer;
use Games\Player;
use MyCLabs\Enum\Enum;
use Games\SendMsg;
use Ratchet\ConnectionInterface;
use function Games\Util\Func\repeat;
use Games\Util\Logging;

abstract class ServerTest {
    use Logging;
    
    abstract protected function msgHandler(Player $player, string $type, $payload = null): void;
    abstract protected function createServer(): GameServer;
    protected function gamesNumber(): int { return 1; }
    
    protected GameServer $server;
    private int $currentGamesNumber = 0;
    
    public function __construct() {
        $this->server = $this->createServer();
    }
    
    public function start(): void {
        $connect = fn() => $this->server->connect(null, null, new Conn([$this, 'fullMsgHandler']));
        repeat($this->server->needPlayersNumber(), $connect);
    }
    
    public function fullMsgHandler(ConnectionInterface $conn, string $type, $payload = null) {
        if ($type === SendMsg::WINNER_IS()->getValue()) {
            $this->currentGamesNumber++;
            if ($this->currentGamesNumber === $this->gamesNumber()) {
                $this->log("Test ended! Number of games: $this->currentGamesNumber");
                exit();
            }
        }
        
        $player = $this->server->players()->get($conn);
        $this->msgHandler($player, $type, $payload);
    }
    
    protected function onMessage($connOrPlayer, Enum $type, $payload = null): void {
        $conn = Player::getConn($connOrPlayer);
        $json = Player::createJsonMsg($type, $payload);
        try {
            $this->server->onMessage($conn, $json);
        } catch (\Exception $e) {
            $this->server->onError($conn, $e);
        }
    }
}
