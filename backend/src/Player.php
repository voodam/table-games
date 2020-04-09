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
    
    public static function getConn(object $connOrPlayer): ConnectionInterface {
        assert($connOrPlayer instanceof self || $connOrPlayer instanceof ConnectionInterface);
        return $connOrPlayer instanceof self ? $connOrPlayer->conn() : $connOrPlayer;
    }
    
    public static function createJsonMsg(Enum $type, $payload = null): string {
        $message = ['type' => $type->getValue()];
        if ($payload) {
            $message['payload'] = $payload;
        }
        return json_encode($message);
    }

    final public function __construct(ConnectionInterface $conn, string $name) {
        $this->conn = $conn;
        $this->name = $name;
    }

    public function send(Enum $message, $payload = null) {
        $json = self::createJsonMsg($message, $payload);
        $this->conn->send($json);
        $this->log("msg to '$this': $json");
    }

    public function closeConn() {
        return $this->conn->close();
    }

    public function conn(): ConnectionInterface { return $this->conn; }
    public function jsonSerialize() { return $this->name; }
    public function __toString(): string { return $this->name; }
}
