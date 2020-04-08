<?php
namespace Games\Card;

use Games\Card\Trick;
use Games\Card\Rank;
use Games\Util\MyObjectStorage;
use function Games\Util\Translate\t;

abstract class Partie {
    protected CardPlayer $eldest;
    protected Suit $trump;
    protected MyObjectStorage $cards; // Team -> Card[]
    protected CardPlayers $players;
    private Trick $trick;

    abstract protected function _score(Team $team): array;
    abstract protected function determEldest(): CardPlayer;

    public function __construct(CardPlayers $players) {
        $this->players = $players;
        $this->cards = new MyObjectStorage;
    }
    
    public function deal(): void {
        $deck = Deck::new32();
        $deck->shuffle();
        $deck->deal($this->players);
        $this->eldest = $this->determEldest();
        assert(isset($this->eldest));
        $this->eldest->send(CardSendMsg::ASK_TRUMP());
        $this->players->sendAbout($this->eldest, CardSendMsg::PLAYER_ASKS_TRUMP());
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

    public function score(Team $team): array {
        if (!$this->ended()) throw new CardException('Party is not over');
        return $this->_score($team);
    }

    public function ended(): bool {
        return !$this->players->haveCards();
    }
    
    public function determTrump(Suit $suit, CardPlayer $eldest = null) { 
        $this->trump = $suit;
        if ($eldest) {
            $this->players->sendOther($eldest, CardSendMsg::TRUMP_IS(), t($this->trump));
        }
    }
    
    public function compareCards(Card $card1, Card $card2): int {
        return $card1->compare($card2, [Rank::class, 'cmpOrder']);
    }
    
    protected function createTrick(CardPlayer $eldest): Trick { return new Trick($this->players, [$this, 'compareCards']); }

    private function getTrick(): void {
        $winner = $this->trick->winner();
        $this->cards->updateInfo($winner->team(), function(?array $cards) {
            $cards ??= [];
            $cards = array_merge($cards, $this->trick->collectCards());
            return $cards;
        });
        
        if (!$this->ended()) {
            $this->newTrick($winner);
        }
    }

    private function newTrick(CardPlayer $eldest): void {
        assert(!isset($this->trick) || $this->trick->ended());
        $this->trick = $this->createTrick($eldest);
        $this->players->sendNext($eldest);
    }
}
