<?php
namespace Games\Card\Goat;

use Games\Card\Trump;
use Games\Card\Card;
use Games\Card\Rank;

class GoatTrump extends Trump {
    public function isTrump(Card $card): bool {
        return parent::isTrump($card) || $card->haveRank(Rank::JACK());
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
