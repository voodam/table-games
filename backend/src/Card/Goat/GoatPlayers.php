<?php
namespace Games\Card\Goat;

use Games\Card\CardPlayers;
use Games\Card\Card;
use Games\Card\Rank;
use Games\Card\Suit;
use Games\Card\CardException;

class GoatPlayers extends CardPlayers {
    public function eldest(): GoatPlayer {
        $eldest = $this->havingCard(new Card(Rank::JACK(), Suit::CLUBS()));
        if (!isset($eldest)) throw new CardException('Can not to determine eldest (probably some cards are out of hands)');
        return $eldest;
    }
    
    protected function playerClass(): string { return GoatPlayer::class; }
}
