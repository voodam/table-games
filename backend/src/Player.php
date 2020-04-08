<?php

namespace Games;

use Ratchet\ConnectionInterface;
use Games\Util\Logging;
use MyCLabs\Enum\Enum;
use Games\GameServer;

class Player implements \JsonSerializable {
    use Logging;

    private ConnectionInterface $conn;
    private string $name;

    final public function __construct(ConnectionInterface $conn, string $name) {
        $this->conn = $conn;
        $this->name = $name;
    }

    public function send(Enum $message, $payload = null) {
        $json = GameServer::createJsonMsg($message, $payload);
        $this->conn->send($json);
        $this->log("send message to {$this->name}: $json");
    }

    public function closeConn() {
        return $this->conn->close();
    }

    public function conn(): ConnectionInterface { return $this->conn; }
    public function jsonSerialize() { return $this->name; }
    public function __toString(): string { return $this->name; }
}
