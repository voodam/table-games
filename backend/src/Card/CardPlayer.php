<?php
namespace Games\Card;

use Games\Player;
use Games\Card\Card;

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
        $key = array_search($this->hand, $card);
        if ($key === false) throw new CardException("No card '$card' in player's '$this' hand");
        unset($this->hand[$key]);
    }
    
    public function hasCard(Card $card): bool {
        return array_search($this->hand, $card) !== false;
    }
    
    public function hasSuit(Suit $suit): bool {
        return count(array_filter($this->hand, fn(Card $card) => $card->suit()->equals($suit))) > 0;
    }
    
    public function hasCards(): bool { return isset($this->hand); }
    public function team(): Team { return $this->team; }
}
