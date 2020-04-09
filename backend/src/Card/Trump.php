<?php
namespace Games\Card;

use Games\Util\Cmp;
use Games\Card\Rank;

class Trump implements \JsonSerializable {
    protected Suit $suit;
    
    public function __construct(Suit $suit) {
        $this->suit = $suit;
    }
    
    public function haveSameSuits(Card $first, Card $second): bool {
        return $first->haveSuit($second);
    }
    
    public function compare(Card $first, Card $second): int {
        $firstIsTrump = $this->isTrump($first);
        $secondIsTrump = $this->isTrump($second);
        
        if ($firstIsTrump && !$secondIsTrump) return Cmp::MORE;
        if ($secondIsTrump && !$firstIsTrump) return Cmp::LESS;
        return $first->compare($second, Rank::DEFAULT_CMP_ORDER);
    }
    
    public function __toString() { return (string) $this->suit; }
    public function jsonSerialize() { return $this->suit; }
    
    protected function isTrump(Card $card): bool {
        return $card->haveSuit($this->suit);
    }
}
