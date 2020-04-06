<?php
namespace Games\Card;

use MyCLabs\Enum\Enum;

class Suit extends Enum implements \JsonSerializable {
    private const DIAMONDS = 'D';
    private const CLUBS = 'C';
    private const HEARTS = 'H';
    private const SPADES = 'S';

    public function jsonSerialize() {
        return $this->getValue();
    }
}
