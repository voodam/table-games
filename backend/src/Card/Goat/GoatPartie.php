<?php
namespace Games\Card\Goat;

use Games\Card\Partie;
use Games\Card\Card;
use Games\Card\Team;
use Games\Card\ScoreCalc;
use Games\Card\Trick;
use Games\Card\Suit;
use Games\Card\Trump;

class GoatPartie extends Partie {
    protected function determEldest(): GoatPlayer {
        return $this->players->eldest();
    }
    
    protected function _score(Team $team): array {
        $cards = $this->cards[$team] ?? [];
        $partieScore = array_reduce($cards, fn(int $score, Card $card) => $score + ScoreCalc::tenAceAndFaceCards($card), 0);

        if ($partieScore === 120) {
            $gameScore = 4;
        } elseif ($partieScore <= 60) {
            $gameScore = 0;
        } else {
            $gameScore = $partieScore > 90 ? 2 : 1;
            if (!$this->eldest->team()->eq($team)) {
                $gameScore *= 2;
            }
        }
        
        return [$gameScore, $partieScore];
    }
    
    protected function createTrump(Suit $suit): Trump { return new GoatTrump($suit); }
    protected function createTrick(): Trick { return new GoatTrick($this->players, $this->trump); }
}
