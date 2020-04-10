<?php
namespace Games\Chess;

use MyCLabs\Enum\Enum;

final class PieceType extends Enum {
    private const BISHOP = 'bishop';
    private const HORSE = 'horse';
    private const KING = 'king';
    private const PAWN ='pawn';
    private const QUEEN = 'queen';
    private const ROOK = 'rook';
}
