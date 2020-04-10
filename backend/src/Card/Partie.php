<?php
namespace Games\Card;

use Games\Util\MyObjectStorage;
use Games\Util\Logging;

abstract class Partie {
    use Logging;
    
    protected CardPlayer $trumpPlayer;
    protected CardPlayer $eldest;
    protected Trump $trump;
    private MyObjectStorage $cardsScore; // Team -> int
    protected CardPlayers $players;
    private Trick $trick;

    abstract public function next(): self;
    abstract protected function calculateGameScore(int $cardsScore, Team $team): int;
    abstract protected function trumpPlayer(): CardPlayer;
    protected function createTrick(): Trick { return new Trick($this->players, $this->trump); }

    public function __construct(CardPlayers $players, CardPlayer $eldest) {
        $this->players = $players;
        $this->eldest = $eldest;
        $this->log("Partie eldest is '$this->eldest'");
        $this->cardsScore = new MyObjectStorage;
        foreach ($this->players->teams() as $team) {
            $this->cardsScore[$team] = 0;
        }
    }
    
    public function deal(): void {
        $deck = Deck::new32();
        $deck->shuffle();
        $deck->deal($this->players);
        $this->trumpPlayer = $this->trumpPlayer();
        $this->log("Partie trump player is '$this->trumpPlayer'");
        assert(isset($this->trumpPlayer));
        $this->trumpPlayer->send(CardSendMsg::ASK_TRUMP(), $this->trumpPlayer);
        $this->players->sendAbout($this->trumpPlayer, CardSendMsg::PLAYER_DETERMS_TRUMP());
    }
    
    public function determineTrump(Trump $trump) { 
        $this->trump = $trump;
        $this->players->sendAll(CardSendMsg::TRUMP_IS(), $this->trump);
        $this->newTrick($this->eldest);
    }

    public function putCard(CardPlayer $player, Card $card): void {
        if (!isset($this->trump)) throw new CardException('Can not make turn while trump is not set');
        if ($this->ended()) throw new CardException('Party is over');
        assert(!$this->trick->ended());
    
        $this->trick->putCard($player, $card);
        if ($this->trick->ended()) {
            $this->getTrick();
        } else {
            $this->players->sendNext($this->players->getNext($player));
        }
    }

    public function gameScore(Team $team): int {
        if (!$this->ended()) throw new CardException('Party is not over');
        return $this->calculateGameScore($this->cardsScore[$team], $team);
    }
    
    public function cardsScore(Team $team): int {
        if (!$this->ended()) throw new CardException('Party is not over');
        return $this->cardsScore[$team];
    }
    
    public function ended(): bool {
        return !$this->players->haveCards();
    }

    private function getTrick(): void {
        $winner = $this->trick->winner();
        $trickScore = $this->trick->calculateScore();
        $this->cardsScore[$winner->team()] += $trickScore;
        $this->players->sendAll(CardSendMsg::TRICK_WINNER_IS(), [$winner, $trickScore]);
        
        if (!$this->ended()) {
            $this->newTrick($winner);
        }
    }

    private function newTrick(CardPlayer $eldest): void {
        assert(!isset($this->trick) || $this->trick->ended());
        $this->trick = $this->createTrick();
        $this->players->sendNext($eldest);
    }
}
