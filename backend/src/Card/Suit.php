<?php
namespace Games\Card;

use MyCLabs\Enum\Enum;
use function Games\Util\Iter\randomValue;

final class Suit extends Enum implements \JsonSerializable {
    private const DIAMONDS = 'diamonds';
    private const CLUBS = 'clubs';
    private const HEARTS = 'hearts';
    private const SPADES = 'spades';
    
    public static function random(): self {
        return randomValue(self::values());
    }

    public function jsonSerialize() { return $this->getValue(); }
}
