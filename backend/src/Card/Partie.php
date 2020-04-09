<?php
namespace Games\Card;

use Games\Util\MyObjectStorage;
use function Games\Util\Translate\t;

abstract class Partie {
    protected CardPlayer $eldest;
    protected Trump $trump;
    private MyObjectStorage $cardsScore; // Team -> int
    protected CardPlayers $players;
    private Trick $trick;

    abstract protected function calculateGameScore(int $cardsScore, Team $team): int;
    abstract protected function determineEldest(): CardPlayer;
    protected function createTrump(Suit $suit): Trump { return new Trump($suit); }
    protected function createTrick(): Trick { return new Trick($this->players, $this->trump); }

    public function __construct(CardPlayers $players) {
        $this->players = $players;
        $this->cardsScore = new MyObjectStorage;
        foreach ($this->players->teams() as $team) {
            $this->cardsScore[$team] = 0;
        }
    }
    
    public function deal(): void {
        $deck = Deck::new32();
        $deck->shuffle();
        $deck->deal($this->players);
        $this->eldest = $this->determineEldest();
        assert(isset($this->eldest));
        $this->eldest->send(CardSendMsg::ASK_TRUMP());
        $this->players->sendAbout($this->eldest, CardSendMsg::PLAYER_DETERMS_TRUMP());
    }
    
    public function determineTrump(Suit $suit) { 
        $this->trump = $this->createTrump($suit);
        $this->players->sendAll(CardSendMsg::TRUMP_IS(), t($this->trump));
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
