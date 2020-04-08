<?php
namespace Games\Card;

class Trump {
    private Suit $suit;
    
    public function __construct(Suit $suit) {
        $this->suit = $suit;
    }
    
    public function isTrump(Card $card): bool {
        return $card->haveSuit($this->suit);
    }
    
    public function __toString() { return (string) $this->suit; }   
}
