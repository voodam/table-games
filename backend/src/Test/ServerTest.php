<?php

namespace Games\Test;

use Games\GameServer;
use Ratchet\ConnectionInterface;
use Games\Players;
use Games\GameServer;
use MyCLabs\Enum\Enum;

abstract class ServerTest {
    abstract public function start(): void;
    abstract protected function createServer(): GameServer;
    
    protected GameServer $server;
    
    public function __construct() {
        $this->server = $this->createServer();
    }

    protected function newConn(): ConnectionInterface {
        $conn = new Conn();
        $this->server->connect(null, null, $conn);
        return $conn;
    }
    
    protected function onMessage($connOrPlayer, Enum $type, $payload = null): void {
        $conn = Players::getConn($connOrPlayer);
        $json = GameServer::createJsonMsg($type, $payload);
        $this->server->onMessage($conn, $json);
    }
}
