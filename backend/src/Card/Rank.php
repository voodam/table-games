<?php
namespace Games\Card;

use MyCLabs\Enum\Enum;
use Games\Util\Compare\Comparable;

class Rank extends Enum implements \JsonSerializable, Comparable {
    private const _2 = '2';
    private const _3 = '3';
    private const _4 = '4';
    private const _5 = '5';
    private const _6 = '6';
    private const _7 = '7';
    private const _8 = '8';
    private const _9 = '9';
    private const _10 = '10';
    private const JACK = 'J';
    private const QUEEN = 'Q';
    private const KING = 'K';
    private const ACE = 'A';

    public function compare(self $other, callable $cmpOrder = null): int {
        $cmpOrder ??= [self::class, 'cmpOrder'];
        return $cmpOrder[$this->getKey()] <=> $cmpOrder[$other->getKey()];
    }

    public function compare10(self $other): int {
        return $this->compare($other, [self::class, 'cmpOrder10']);
    }

    public function jsonSerialize() { return $this->getValue(); }

    public static function cmpOrder(): array {
        static $order = null;
        $order ??= array_flip(self::keys());
        return $order;
    }

    public static function cmpOrder10(): array {
        static $order = null;
        if (!$order) {
            $keys = self::keys();
            $ten = $keys[8];
            unset($keys[8]);
            array_splice($keys, 11, 0, $ten);
            $order = array_flip($keys);
        }
        return $order;
    }
}
