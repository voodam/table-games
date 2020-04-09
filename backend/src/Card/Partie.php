<?php
namespace Games\Card;

use Games\Util\MyObjectStorage;
use function Games\Util\Translate\t;

abstract class Partie {
    protected CardPlayer $eldest;
    protected Trump $trump;
    private MyObjectStorage $score; // Team -> int
    protected CardPlayers $players;
    private Trick $trick;

    abstract protected function gameScore(int $partieScore, Team $team): int;
    abstract protected function determEldest(): CardPlayer;
    protected function createTrump(Suit $suit): Trump { return new Trump($suit); }
    protected function createTrick(): Trick { return new Trick($this->players, $this->trump); }

    public function __construct(CardPlayers $players) {
        $this->players = $players;
        $this->score = new MyObjectStorage;
        foreach ($this->players->teams() as $team) {
            $this->score[$team] = 0;
        }
    }
    
    public function deal(): void {
        $deck = Deck::new32();
        $deck->shuffle();
        $deck->deal($this->players);
        $this->eldest = $this->determEldest();
        assert(isset($this->eldest));
        $this->eldest->send(CardSendMsg::ASK_TRUMP());
        $this->players->sendAbout($this->eldest, CardSendMsg::PLAYER_DETERMS_TRUMP());
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

    public function score(Team $team): array {
        if (!$this->ended()) throw new CardException('Party is not over');
        $partieScore = $this->score[$team];
        return [$this->gameScore($partieScore, $team), $partieScore];
    }

    public function ended(): bool {
        return !$this->players->haveCards();
    }
    
    public function determTrump(Suit $suit) { 
        $this->trump = $this->createTrump($suit);
        $this->players->sendAll(CardSendMsg::TRUMP_IS(), t($this->trump));
        $this->newTrick($this->eldest);
    }

    private function getTrick(): void {
        $winner = $this->trick->winner();
        $trickScore = $this->trick->score();
        $this->score[$winner->team()] += $trickScore;
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
