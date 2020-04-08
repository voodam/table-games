<?php
namespace Games\Card\Goat;

use Games\Card\Partie;
use Games\Card\Card;
use Games\Card\Team;
use Games\Card\ScoreCalc;
use Games\Card\Trick;
use Games\Card\CardPlayer;

class GoatPartie extends Partie {
    protected function determEldest(): CardPlayer {
        return $this->players->getNext($this->players->getNext($this->players->getNext($this->players->getFirst()))); //!
        return $this->players->havingCard( new Card(Rank::JACK(), Suit::CLUBS()) );
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
    
    public function compareCards(Card $card1, Card $card2): int {
        return $card1->compareTrump($card2, $this->trump, [Rank::class, 'cmpOrder10']);
    }
    
    protected function createTrick(CardPlayer $eldest): Trick { return new GoatTrick($eldest, $this->players, [$this, 'compareCards']); }
}
