<?php
namespace Games\Chess;

use Games\GameServer;
use Games\Player;
use Games\Chess\ChessRecvMsg;

class ChessServer extends GameServer {
    private Board $board;

    public function __construct() {
        parent::__construct(2);
        $this->board = new Board();
        $this->attachObserver($this, ChessRecvMsg::MOVE_PIECE());
    }

    protected function startGame() {
        $this->board->setupPieces();
        $pieces = iterator_to_array($this->board->pieces());
        foreach ($this->players as $player) {
            $player->send(ChessSendMsg::CREATE_PIECES(), $pieces);
        }

        $this->players->sendNext($this->players->getFirst());
    }
    
    protected function movePiece(array $coordsPair, Player $currentPlayer) {
        $otherPlayer = $this->players->getNext($currentPlayer);
        $otherPlayer->send(ChessSendMsg::MOVE_PIECES(), [$coordsPair]);
        $this->players->sendNext($otherPlayer);
    }
}
