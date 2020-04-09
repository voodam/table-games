<?php
namespace Games\Card;

class Trump {
    protected Suit $suit;
    
    protected function _compareTrumps(Card $first, Card $second): int {
        return $first->compare($second);
    }
    
    public function __construct(Suit $suit) {
        $this->suit = $suit;
    }
    
    public function isTrump(Card $card): bool {
        return $card->haveSuit($this->suit);
    }
    
    final public function compareTrumps(Card $first, Card $second): int {
        assert($this->isTrump($first));
        assert($this->isTrump($second));
        return $this->_compareTrumps($first, $second);
    }
    
    public function __toString() { return (string) $this->suit; }   
}
