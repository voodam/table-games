(() => {
const controls = document.querySelector('#controls');
const ctrl = new GameController(GameController.createDefaultElems(controls, false));
const table = createTable([8, 8], document.getElementById('board'));

ctrl.onPlay(conn => {
    const board = new Board(table);
    conn.onClose(board.clear.bind(board));

    conn.on(RecvMsg.CREATE_PIECES, (pieces) => {
        for (const [piece, coords] of pieces) {
            board.createPiece(Piece.fromDict(piece), Coords.fromDict(coords));
        }
    });
    conn.on(RecvMsg.REMOVE_PIECE, (coords) => board.removePiece(Coords.fromDict(coords)));
    conn.on(RecvMsg.MOVE_PIECES, (coordsPairs) => {
        board.clearHighlight();

        for (let {from, to} of coordsPairs) {
            from = Coords.fromDict(from);
            to = Coords.fromDict(to);
            board.movePiece(from, to);
            board.highlightMove(from, to);
        }
    });

    board.onMove((from, to) => {
        conn.send(SendMsg.MOVE_PIECE, {from, to});
        board.lock();
    });

    conn.on(WebsocketConn.RecvMsg.YOUR_TURN, board.unlock.bind(board));
    conn.on(WebsocketConn.RecvMsg.TURN_OF, board.lock.bind(board));
});
})();

