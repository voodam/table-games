<?php
namespace Games\Chess;

final class Piece implements \JsonSerializable {
    private PieceType $type;
    private Color $color;

    public function __construct(PieceType $type, Color $color) {
        $this->type = $type;
        $this->color = $color;
    }

    public function jsonSerialize() { return ['type' => $this->type, 'color' => $this->color]; }
}
