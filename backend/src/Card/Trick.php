<?php
namespace Games\Card;

use Games\Util\Compare\Comparable;
use Games\Util\MyObjectStorage;
use Games\Card\CardSendMsg;

class Trick {
    private CardPlayers $players;
    protected Suit $trump;
    protected MyObjectStorage $cards; // Card -> CardPlayer
 
    public function __construct(CardPlayer $eldest, CardPlayers $players, Suit $trump) {
        $this->players = $players;
        $this->trump = $trump;
        $this->cards = new MyObjectStorage;
        $this->players->sendNext($eldest);
    }
    
    public function putCard(CardPlayer $player, Card $card): void {
        if ($this->ended()) throw new CardException('Trick is ended');
        if (!$this->players->contain($player)) throw new \OutOfBoundsException("Player '$player' not in company of this trick players :-(");
        $this->constrainCard($player, $card);
        $player->putCard($card);
        $this->players->sendOther($player, CardSendMsg::HE_PUTS_CARD(), ['player' => $player, 'card' => $card]);
        $this->cards[$card] = $player;
    }

    public function winner(): CardPlayer {
        if (!$this->ended()) throw new CardException('Trick is not over, so there is no winner');
        $maxCard = array_reduce($this->cards, fn(Card $maxCard, Card $card) => $this->compareCards($maxCard, $card) === Comparable::LESS ? $card : $maxCard);
        return $this->cards[$maxCard];
    }

    public function collectCards(): array { 
        if (!$this->ended()) throw new CardException('Can not collect cards: trick is not over');
        return iterator_to_array($this->cards);
    }

    public function ended(): bool { 
        return count($this->cards) >= count($this->players);
    }
    
    protected function compareCards(Card $card1, Card $card2): int {
        return $card1->compare($card2, [Rank::class, 'cmpOrder']);
    }
    
    protected function constrainCard(CardPlayer $player, Card $card): void {}
}
