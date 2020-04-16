<?php
namespace Games\Card\Goat;

use Games\GameServer;
use Games\Card\CardRecvMsg;
use Games\Card\Card;
use Games\Card\Suit;
use function Games\Util\Func\_new;
use function Games\Util\Func\compose;
use Games\Card\CardPlayers;
use Ratchet\ConnectionInterface;

class GoatServer extends GameServer {
    private Goat $game;
    
    public function __construct(array $initialTeamScore = []) {
        parent::__construct(4, $initialTeamScore);
    }
    
    protected function startGame() {
        $this->preparePayload(CardRecvMsg::PUT_CARD(), [Card::class, 'fromPair']);
        $this->preparePayload(CardRecvMsg::DETERMINE_TRUMP(), compose(_new(GoatTrump::class), _new(Suit::class)) );
        
        $this->detachObserver($this->game ?? null, CardRecvMsg::PUT_CARD());
        $this->game = new Goat($this->players, $this->initialTeamScore);
        $this->attachObserver($this->game, CardRecvMsg::PUT_CARD());
        $this->game->start();
    }
    
    private $defaultPlayers = ['Мама', 'Папа'];
    public function onClose(ConnectionInterface $conn) {
        parent::onClose($conn);
        $this->defaultPlayers = ['Мама', 'Папа'];
    }
    
    protected function defaultPlayerName(): string {
        return array_shift($this->defaultPlayers) ?? parent::defaultPlayerName();
    }
    
    protected function createPlayers(int $maxPlayers): CardPlayers { return new CardPlayers($maxPlayers); }
}
