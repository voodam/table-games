(() => {
const controls = document.getElementById('controls');
const ctrl = new GameController(GameController.createDefaultElems(controls, false));
const table = createTable([8, 8], document.getElementById('board'));

ctrl.onPlay(conn => {
    conn.preparePayload({
        [RecvMsg.CREATE_PIECES]: curry(mapPairs)(Piece.fromDict, Coords.fromDict),
        [RecvMsg.REMOVE_PIECE]: Coords.fromDict,
        [RecvMsg.MOVE_PIECES]: curry(mapPairs)(Coords.fromDict, Coords.fromDict)
    });
    
    const board = new Board(table);
    ctrl.lockOnTurns(conn, board);
    conn.onClose(board.clear.bind(board));

    board.onMove((from, to) => {
        conn.send(SendMsg.MOVE_PIECE, [from, to]);
        board.lock();
    });
    conn.on(RecvMsg.CREATE_PIECES, (pieces) => {
        for (const [piece, coords] of pieces) {
            board.createPiece(piece, coords);
        }
    });
    conn.on(RecvMsg.REMOVE_PIECE, board.removePiece.bind(board));
    conn.on(RecvMsg.MOVE_PIECES, (coordsPairs) => {
        board.clearHighlight();

        for (let [from, to] of coordsPairs) {
            board.movePiece(from, to);
            board.highlightMove(from, to);
        }
    });
});
})();
