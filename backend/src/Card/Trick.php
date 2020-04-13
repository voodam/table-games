<?php
namespace Games\Card;

use Games\Util\Cmp;
use Games\Util\MyObjectStorage;
use Games\Util\Logging;
use Games\Card\ScoreCalc;

class Trick {
    use Logging;
    
    protected MyObjectStorage $cards; // Card -> CardPlayer
    private CardPlayers $players;
    protected Trump $trump;
    
    protected function constrainCard(CardPlayer $player, Card $putCard): void {}
    
    public function __construct(CardPlayers $players, Trump $trump) {
        $this->players = $players;
        $this->trump = $trump;
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
    
    public function collectCards(): array { 
        if (!$this->ended()) throw new CardException('Can not collect cards: trick is not over');
        return iterator_to_array($this->cards);
    }
    
    public function calculateScore(): int {
        if (!$this->ended()) throw new CardException('Can not collect cards: trick is not over');
        return array_reduce(iterator_to_array($this->cards), fn(int $score, Card $card) => $score + ScoreCalc::tenAceAndFaceCards($card), 0);
    }

    public function winner(): CardPlayer {
        if (!$this->ended()) throw new CardException('Trick is not over, so there is no winner');
        
        $selectMaxCard = function (?Card $maxCard, Card $card) {
            if (!isset($maxCard)) return $card;
            return $this->trump->compare($maxCard, $card) === Cmp::LESS ? $card : $maxCard;
        };
        $maxCard = array_reduce(iterator_to_array($this->cards), $selectMaxCard);
        return $this->cards[$maxCard];
    }

    public function ended(): bool { 
        return count($this->cards) >= count($this->players);
    }
}
