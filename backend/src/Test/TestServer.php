<?php

namespace Games\Test;

use Games\GameServer;
use Ratchet\ConnectionInterface;

abstract class TestServer {
    abstract public function start(): void;
    abstract protected function createServer(): GameServer;
    
    private GameServer $server;
    
    public function __construct() {
        $this->server = $this->createServer();
    }
    
    protected function newConn(): ConnectionInterface {
        $conn = new Conn();
        $this->server->connect(null, null, $conn);
        return $conn;
    }
    
    protected function onMessage(ConnectionInterface $conn, string $type, $payload = null): void {
        $json = json_encode(['type' => $type, 'payload' => $payload]);
        $this->server->onMessage($conn, $json);
    }
}
