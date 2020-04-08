<?php
namespace Games\Card;

use Games\Player;
use Games\Util\Cmp;
use function Games\Util\Iter\randomValue;

class CardPlayer extends Player {
    private Team $team;
    /**
     * @var Card[]
     */
    private array $hand = [];

    public function joinTeam(Team $team) {
        if (isset($this->team)) throw new \DomainException("Player '$this' has team already");
        $this->team = $team;
        $this->send(CardSendMsg::YOUR_TEAM(), $team);
    }

    public function deal(array $hand): void {
        $this->hand = $hand;
        $this->send(CardSendMsg::DEAL(), $hand);
    }

    public function putCard(Card $card): void {
        if (!$this->hasCards()) throw new CardException("No cards in player's '$this' hand");
        $key = $this->getCardIndex($card);
        if ($key === false) throw new CardException("No card '$card' in player's '$this' hand");
        unset($this->hand[$key]);
        $this->log("'$this' has cards left: " . implode(', ', $this->hand));
    }
    
    public function hasCard(Card $card): bool {
        return $this->getCardIndex($card) !== false;
    }
    
    public function hasSuitOf(Card $card, Trump $trump = null): bool {
        return !empty(array_filter($this->hand, fn(Card $c) => $card->haveSuitOf($c, $trump)));
    }
    
    public function randomCard(): Card { return randomValue($this->hand); }
    public function hasCards(): bool { return !empty($this->hand); }
    public function team(): Team { return $this->team; }
    
    private function getCardIndex(Card $card) {
        foreach ($this->hand as $index => $c) {
            if ($c->compare($card) === Cmp::EQ) return $index;
        }
        return false;
    }
}
