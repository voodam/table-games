<?php
namespace Games\Test\Doubles;

use Ratchet\ConnectionInterface;
use Games\Util\Func\noop;

class ConnStub implements ConnectionInterface {
    private $msgHandler; 
    
    public function __construct(callable $msgHandler = null) {
        $msgHandler ??= noop::class;
        $this->msgHandler = $msgHandler;
    }
    
    public function send($data) {
        $message = json_decode($data, true);
        ($this->msgHandler)($this, $message['type'], $message['payload'] ?? null);
    }

    public function close() {}
}
