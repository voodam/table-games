<?php
namespace Games\Card\Goat;

use Games\GameServer;
use Games\Card\CardRecvMsg;
use Games\Card\Card;
use Games\Card\Suit;
use function Games\Util\Func\_new;
use Games\Card\CardPlayers;

class GoatServer extends GameServer {
    private Goat $game;
    
    public function __construct() {
        parent::__construct(4);
    }
    
    protected function startGame() {
        $this->preparePayload(CardRecvMsg::PUT_CARD(), [Card::class, 'fromPair']);
        $this->preparePayload(CardRecvMsg::DETERMINE_TRUMP(), _new(Suit::class));
        
        $this->detachObserver($this->game ?? null, CardRecvMsg::PUT_CARD());
        $this->game = new Goat($this->players);
        $this->attachObserver($this->game, CardRecvMsg::PUT_CARD());
        $this->game->start();
    }
    
    protected function createPlayers(): CardPlayers { return new CardPlayers; }
}
