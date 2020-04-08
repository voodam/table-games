<?php

namespace Games\Test;

use Games\Card\Goat\GoatServer;
use Games\Card\Card;
use Games\Card\Suit;
use Games\Card\Rank;

class GoatServerTest extends ServerTest {
    public function start(): void {
        $this->newConn();
        $this->newConn();
        $this->newConn();
        $this->newConn();
        
        $players = $this->players();
        $eldest = $players->havingCard( new Card(Rank::JACK(), Suit::CLUBS()) );
        $this->onMessage($eldest, $type);
        $card = $this->randomCard($eldest);
    }
    
    protected function createServer(): GoatServer { return new GoatServer; }
}
 