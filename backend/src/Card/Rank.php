<?php
namespace Games\Card;

use MyCLabs\Enum\Enum;

final class Rank extends Enum implements \JsonSerializable {
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
    
    public const DEFAULT_CMP_ORDER = [self::class, 'cmpOrder'];
    
    public static function keys() {
        $keys = parent::keys();
        $index = array_search('DEFAULT_CMP_ORDER', $keys);
        unset($keys[$index]);
        return $keys;
    }

    public static function cmpOrder(): array {
        static $order = null;
        $order ??= array_flip(self::keys());
        return $order;
    }

    public static function cmpOrder10(): array {
        static $order = null;
        if (!isset($order)) {
            $keys = self::keys();
            $ten = $keys[8];
            unset($keys[8]);
            array_splice($keys, 11, 0, $ten);
            $order = array_flip($keys);
        }
        return $order;
    }

    public function compare(self $other, callable $cmpOrder = self::DEFAULT_CMP_ORDER): int {
        $order = $cmpOrder();
        return $order[$this->getKey()] <=> $order[$other->getKey()];
    }

    public function jsonSerialize() { return $this->getValue(); }
}
