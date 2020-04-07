<?php

namespace Games\Test;

use Games\Card\Goat\GoatServer;

class GoatServerTest extends ServerTest {
    public function start(): void {
        $player1 = $this->newConn();
        $player2 = $this->newConn();
        $player3 = $this->newConn();
        $player4 = $this->newConn();
    }
    
    protected function createServer(): GoatServer { return new GoatServer; }
}
