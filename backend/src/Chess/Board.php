<?php
namespace Games\Chess;

class Board {
    private array $pieces = [];

    public function __construct() {
        $this->resetPieces();
    }

    public function setupPieces() {
        $this->resetPieces();
        $this->setupSet(Color::WHITE(), 2, 1);
        $this->setupSet(Color::BLACK(), 7, 8);
    }

    public function pieces(): \Traversable {
        foreach (range('A', 'H') as $letter) {
            foreach ($this->pieces[$letter] as $number => $piece) {
                yield [$piece, new Coords($letter, $number)];
            }
        }
    }

    private function setupSet(Color $color, int $pawnCoord, int $notPawnCoord) {
        $this->pieces['A'][$notPawnCoord] = new Piece(PieceType::ROOK(), $color);
        $this->pieces['B'][$notPawnCoord] = new Piece(PieceType::HORSE(), $color);
        $this->pieces['C'][$notPawnCoord] = new Piece(PieceType::BISHOP(), $color);
        $this->pieces['D'][$notPawnCoord] = new Piece(PieceType::QUEEN(), $color);
        $this->pieces['E'][$notPawnCoord] = new Piece(PieceType::KING(), $color);
        $this->pieces['F'][$notPawnCoord] = new Piece(PieceType::BISHOP(), $color);
        $this->pieces['G'][$notPawnCoord] = new Piece(PieceType::HORSE(), $color);
        $this->pieces['H'][$notPawnCoord] = new Piece(PieceType::ROOK(), $color);
        foreach (range('A', 'H') as $letter) {
            $this->pieces[$letter][$pawnCoord] = new Piece(PieceType::PAWN(), $color);
        }
    }

    private function resetPieces() {
        foreach (range('A', 'H') as $letter) {
            $this->pieces[$letter] = [];
        }
    }
}
