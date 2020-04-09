<?php
namespace Games\Card\Goat;

use Games\Card\Trump;
use Games\Card\Card;
use Games\Card\Rank;
use Games\Card\Suit;
use Games\Util\Cmp;

class GoatTrump extends Trump {
    public function haveSameSuits(Card $first, Card $second): bool {
        $firstIsTrump = $this->isTrump($first);
        $secondIsTrump = $this->isTrump($second);
        
        if ($firstIsTrump && $secondIsTrump) return true;
        if ($firstIsTrump && !$secondIsTrump || !$firstIsTrump && $secondIsTrump) return false;
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
        
        foreach ($this->specialTrumps() as $card) {
            if ($first->eq($card)) {
                return Cmp::MORE;
            }
            if ($second->eq($card)) {
                return Cmp::LESS;
            }
        }
        
        return $this->compareDefault($first, $second);
    }
    
    private $specialTrumps;
    private function specialTrumps(): array {
        return $this->specialTrumps ??= [
            new Card(Rank::_7(), $this->suit), 
            new Card(Rank::JACK(), Suit::CLUBS()),
            new Card(Rank::JACK(), Suit::SPADES()),
            new Card(Rank::JACK(), Suit::HEARTS()),
            new Card(Rank::JACK(), Suit::DIAMONDS()),
        ];
    }
    
    private function compareDefault(Card $first, Card $second): int {
        return $first->compare($second, [Rank::class, 'cmpOrder10']);
    }
}
