<?php
namespace Games\Card\Goat;

use Games\GameServer;
use Games\Card\CardRecvMsg;
use Games\Card\Card;

class GoatServer extends GameServer {
    public function __construct() {
        parent::__construct(4);
    }
    
    protected function startGame() {
        $this->preparePayload(CardRecvMsg::PUT_CARD(), [Card::class, 'fromDict']);
        $this->preparePayload(CardRecvMsg::DETERM_TRUMP(), _new(Suit::class));
        
        $game = new Goat($this->players);
        $this->attachObserver($game, CardRecvMsg::PUT_CARD());
        $game->start();   
    }
}
