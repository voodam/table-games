<?php
namespace Games\Test;

use Games\Chess\ChessServer;
use Games\Chess\ChessRecvMsg;
use Games\Chess\Coords;

class ChessServerTest extends ServerTest {
    public function start(): void {
//        $this->onMessage($player1, ChessRecvMsg::MOVE_PIECE(), [new Coords('E', 2), new Coords('E', 4)]);
//        $this->onMessage($player2, ChessRecvMsg::MOVE_PIECE(), [new Coords('E', 7), new Coords('E', 5)]);
    }

    protected function createServer(): ChessServer { return new ChessServer; }

    protected function msgHandler(\Games\Player $player, string $type, $payload = null): void {   
    }
}
