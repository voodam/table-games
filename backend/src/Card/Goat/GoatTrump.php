<?php
namespace Games\Card\Goat;

use Games\Card\Trump;
use Games\Card\Card;
use Games\Card\Rank;
use Games\Card\Suit;
use Games\Util\Cmp;

class GoatTrump extends Trump {
    public function haveSameSuits(Card $first, Card $second): bool {
        if ($this->isTrump($first) && $this->isTrump($second)) {
            return true;
        }
        return parent::haveSameSuits($first, $second);
    }
    
    public function compare(Card $first, Card $second): int {
        $firstIsTrump = $this->isTrump($first);
        $secondIsTrump = $this->isTrump($second);
        
       
        if (!$firstIsTrump && !$secondIsTrump) return $this->compareDefault($first, $second);
        if ($firstIsTrump && $secondIsTrump) return $this->compareTrumps($first, $second);
        return parent::compare($first, $second);
    }
    
    protected function isTrump(Card $card): bool {
        return parent::isTrump($card) || $card->haveRank(Rank::JACK());
    }
    
    private function compareTrumps(Card $first, Card $second): int {
        assert($this->isTrump($first));
        assert($this->isTrump($second));
        
        static $specialCards = null;
        $specialCards ??= [
            new Card(Rank::_7(), $this->suit), 
            new Card(Rank::JACK(), Suit::CLUBS()),
            new Card(Rank::JACK(), Suit::SPADES()),
            new Card(Rank::JACK(), Suit::HEARTS()),
            new Card(Rank::JACK(), Suit::DIAMONDS()),
        ];
        
        foreach ($specialCards as $card) {
            if ($first->compare($card) == Cmp::EQ) {
                return Cmp::MORE;
            }
            if ($second->compare($card) == Cmp::EQ) {
                return Cmp::LESS;
            }
        }
        
        return $this->compareDefault($first, $second);
    }
    
    private function compareDefault(Card $first, Card $second): int {
        return $first->compare($second, [Rank::class, 'cmpOrder10']);
    }
}
