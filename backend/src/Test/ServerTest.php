<?php

namespace Games\Test;

use Games\GameServer;
use Games\Player;
use MyCLabs\Enum\Enum;
use Games\SendMsg;
use Ratchet\ConnectionInterface;
use function Games\Util\Func\repeat;

abstract class ServerTest {
    abstract protected function msgHandler(Player $player, string $type, $payload = null): void;
    abstract protected function createServer(): GameServer;
    
    protected GameServer $server;
    
    public function __construct() {
        $this->server = $this->createServer();
    }
    
    public function start(): void {
        $connect = fn() => $this->server->connect(null, null, new Conn([$this, 'fullMsgHandler']));
        repeat($this->server->needPlayersNumber(), $connect);
    }
    
    public function fullMsgHandler(ConnectionInterface $conn, string $type, $payload = null) {
        switch ($type) {
            case SendMsg::WINNER_IS()->getValue():
                echo "Test ended!\n";
                exit();
        }
        
        $player = $this->server->players()->get($conn);
        $this->msgHandler($player, $type, $payload);
    }
    
    protected function onMessage($connOrPlayer, Enum $type, $payload = null): void {
        $conn = Player::getConn($connOrPlayer);
        $json = GameServer::createJsonMsg($type, $payload);
        try {
            $this->server->onMessage($conn, $json);
        } catch (\Exception $e) {
            $this->server->onError($conn, $e);
        }
    }
}
