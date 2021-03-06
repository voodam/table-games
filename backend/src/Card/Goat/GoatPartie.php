<?php
namespace Games\Card\Goat;

use Games\Card\Partie;
use Games\Team;
use Games\Card\TrickInterface;
use Games\Card\Suit;
use Games\Card\Rank;
use Games\Card\Card;
use Games\Card\CardPlayer;

class GoatPartie extends Partie {
    protected function trumpPlayer(): CardPlayer {
        $trumpPlayer = $this->players->havingCard(new Card(Rank::JACK(), Suit::CLUBS()));
        if (!isset($trumpPlayer)) throw new CardException('Can not to determine trump player (probably some cards are out of hands)');
        return $trumpPlayer;
    }
    
    protected function calculateGameScore(int $cardsScore, Team $team): int {
        $thisTeamOfTrumpPlayer = $this->trumpPlayer->hasTeam($team);
        if ($cardsScore === 120) {
            $otherTeam = $this->players->getOtherTeam($team);
            return $thisTeamOfTrumpPlayer && $this->gotAnyTrick($otherTeam) ? 2 : 4;
        }
        if ($cardsScore <= 60) {
            return 0;
        }
        
        $score = $cardsScore > 90 ? 2 : 1;
        return $thisTeamOfTrumpPlayer ? $score : $score * 2;
    }
    
    protected function createTrick(): TrickInterface { return new GoatTrick($this->players, $this->trump); }
}
