<?php
namespace Games\Card\Goat;

use Games\Card\Trump;
use Games\Card\Card;
use Games\Card\Rank;

class GoatTrump extends Trump {
    public function isTrump(Card $card): bool {
        return parent::isTrump($card) || $card->haveRank(Rank::JACK());
    }
}
