<?php
namespace Games\Card;

use function Games\Util\Iter\getFirstKey;

class ScoreCalc {
    public static function tenAceAndFaceCards(Card $card): int {
        static $score = null;
        $score ??= [10 => Rank::_10(), 2 => Rank::JACK(), 3 => Rank::QUEEN(), 4 => Rank::KING(), 11 => Rank::ACE()];
        return getFirstKey($score, [$card->rank(), 'equals'], 0);
    }
}
