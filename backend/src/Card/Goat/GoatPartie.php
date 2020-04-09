<?php
namespace Games\Card\Goat;

use Games\Card\Partie;
use Games\Card\Team;
use Games\Card\Trick;
use Games\Card\Suit;
use Games\Card\Rank;
use Games\Card\Card;
use Games\Card\Trump;
use Games\Card\CardPlayer;

class GoatPartie extends Partie {
    protected function determineEldest(): CardPlayer {
        $eldest = $this->players->havingCard(new Card(Rank::JACK(), Suit::CLUBS()));
        if (!isset($eldest)) throw new CardException('Can not to determine eldest (probably some cards are out of hands)');
        return $eldest;
    }
    
    protected function calculateGameScore(int $cardsScore, Team $team): int {
        if ($cardsScore === 120) {
            $gameScore = 4;
        } elseif ($cardsScore <= 60) {
            $gameScore = 0;
        } else {
            $gameScore = $cardsScore > 90 ? 2 : 1;
            if (!$this->eldest->team()->eq($team)) {
                $gameScore *= 2;
            }
        }
        
        return $gameScore;
    }
    
    protected function createTrump(Suit $suit): Trump { return new GoatTrump($suit); }
    protected function createTrick(): Trick { return new GoatTrick($this->players, $this->trump); }
}
