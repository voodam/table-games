<?php
namespace Games\Card;

use Games\Util\Cmp;
use Games\Util\MyObjectStorage;
use Games\Card\CardSendMsg;
use Games\Util\Logging;

class Trick {
    use Logging;
    
    protected MyObjectStorage $cards; // Card -> CardPlayer
    private CardPlayers $players;
    private $compareCards;

    public function __construct(CardPlayers $players, callable $compareCards) {
        $this->players = $players;
        $this->compareCards = $compareCards;
        $this->cards = new MyObjectStorage;
    }
    
    public function putCard(CardPlayer $player, Card $card): void {
        if ($this->ended()) throw new CardException('Trick is ended');
        if (!$this->players->contains($player)) throw new \OutOfBoundsException("Player '$player' not in company of this trick players :-(");
        $this->constrainCard($player, $card);
        $player->putCard($card);
        $this->players->sendOther($player, CardSendMsg::PLAYER_PUTS_CARD(), $card);
        $this->cards[$card] = $player;
    }

    public function winner(): CardPlayer {
        if (!$this->ended()) throw new CardException('Trick is not over, so there is no winner');
        
        $selectMaxCard = function (?Card $maxCard, Card $card) {
            if (!isset($maxCard)) return $card;
            return ($this->compareCards)($maxCard, $card) === Cmp::LESS ? $card : $maxCard;
        };
        $maxCard = array_reduce(iterator_to_array($this->cards), $selectMaxCard);
        return $this->cards[$maxCard];
    }

    public function collectCards(): array { 
        if (!$this->ended()) throw new CardException('Can not collect cards: trick is not over');
        return iterator_to_array($this->cards);
    }

    public function ended(): bool { 
        return count($this->cards) >= count($this->players);
    }
    
    protected function constrainCard(CardPlayer $player, Card $card): void {}
}
