<?php
namespace Games\Card\Goat;

use Games\Card\Trump;
use Games\Card\Card;
use Games\Card\Rank;
use Games\Card\Suit;
use Games\Util\Cmp;

class GoatTrump extends Trump {
    public function isTrump(Card $card): bool {
        return parent::isTrump($card) || $card->haveRank(Rank::JACK());
    }
    
    protected function _compareTrumps(Card $first, Card $second): int {
        $specialCards = [
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
        
        return $first->compare($second);
    }
    
    public function rankCmpOrder(): array {
        static $order = null;
        if (!isset($order)) {
            $keys = Rank::keys();
            $ten = $keys[8];
            unset($keys[8]);
            array_splice($keys, 11, 0, $ten);
            $seven = $keys[5];
            unset($keys[5]);
            array_splice($keys, 12, 0, $seven);
            $order = array_flip($keys);
        }
        return $order;
    }
}
