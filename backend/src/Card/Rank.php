<?php
namespace Games\Card;

use MyCLabs\Enum\Enum;
use Games\Util\Compare\Comparable;

class Rank extends Enum implements \JsonSerializable, Comparable {
    private const _2 = 'two';
    private const _3 = 'three';
    private const _4 = 'four';
    private const _5 = 'five';
    private const _6 = 'six';
    private const _7 = 'seven';
    private const _8 = 'eight';
    private const _9 = 'nine';
    private const _10 = 'ten';
    private const JACK = 'jack';
    private const QUEEN = 'queen';
    private const KING = 'king';
    private const ACE = 'ace';

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
