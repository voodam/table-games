<?php
namespace Games\Test;

use Ratchet\ConnectionInterface;

class Conn implements ConnectionInterface {
    private $msgHandler; 
    
    public function __construct(callable $msgHandler) {
        $this->msgHandler = $msgHandler;
    }
    
    public function send($data) {
        $message = json_decode($data, true);
        ($this->msgHandler)($this, $message['type'], $message['payload'] ?? null);
    }

    public function close() {}
}
