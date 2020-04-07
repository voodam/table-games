<?php

namespace Games\Test;

use Games\Chess\ChessServer;
use Games\GameServer;
use Games\Chess\ChessRecvMsg;
use Games\Chess\Coords;

class ChessTestServer extends TestServer {
    public function start(): void {
        $player1 = $this->newConn();
        $player2 = $this->newConn();
        $this->onMessage($player1, ChessRecvMsg::MOVE_PIECE(), [new Coords('E', 2), new Coords('E', 4)]);
        $this->onMessage($player2, ChessRecvMsg::MOVE_PIECE(), [new Coords('E', 7), new Coords('E', 5)]);
        $this->newConn();
    }

    protected function createServer(): GameServer { return new ChessServer; }
}
