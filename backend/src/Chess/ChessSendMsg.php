<?php
namespace Games\Chess;

use MyCLabs\Enum\Enum;

class ChessSendMsg extends Enum {
    private const CREATE_PIECES = 'createPieces';
    private const MOVE_PIECES = 'movePieces';
    private const REMOVE_PIECE = 'removePiece';
}
