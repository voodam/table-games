<?php
namespace Games\Card\Goat;

use Games\Card\Partie;
use Games\Card\Card;
use Games\Card\Team;
use Games\Card\ScoreCalc;
use Games\Card\Trick;

class GoatPartie extends Partie {
    protected function _score(Team $team): array {
        $cards = $this->cards[$team] ?? [];
        $cardScore = array_reduce($cards, fn(int $score, Card $card) => $score + ScoreCalc::tenAceAndFaceCards($card), 0);

        if ($cardScore === 120) {
            $partieScore = 4;
        } elseif ($cardScore <= 60) {
            $partieScore = 0;
        } else {
            $partieScore = $cardScore > 90 ? 2 : 1;
            if (!$this->eldest->team()->eq($team)) {
                $partieScore *= 2;
            }
        }
        
        return [$partieScore, $cardScore];
    }
    
    protected function createTrick(CardPlayer $eldest): Trick { return new GoatTrick($eldest, $this->players, $this->trump); }
}
