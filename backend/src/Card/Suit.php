<?php
namespace Games\Card;

use MyCLabs\Enum\Enum;

class Suit extends Enum implements \JsonSerializable {
    private const DIAMONDS = 'diamonds';
    private const CLUBS = 'clubs';
    private const HEARTS = 'hearts';
    private const SPADES = 'spades';

    public function jsonSerialize() { return $this->getValue(); }
}
