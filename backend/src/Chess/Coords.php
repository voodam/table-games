<?php
namespace Games\Chess;

class Coords implements \JsonSerializable {
    private string $letter;
    private int $number;

    public function __construct(string $letter, int $number) {
        $this->letter = $letter;
        $this->number = $number;
    }

    public function jsonSerialize() {
        return ['letter' => $this->letter, 'number' => $this->number];
    }
}
