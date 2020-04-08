(() => {
const controls = document.querySelector('#controls');
const ctrl = new GameController(GameController.createDefaultElems(controls));

ctrl.onPlay(conn => {
    conn.preparePayload({
        [RecvMsg.DEAL]: hand => hand.map(Card.fromDict),
        [RecvMsg.PLAYER_PUTS_CARD]: curry(mapDict)({player: id, card: Card.fromDict})
    });
    ctrl.messagesOn(conn, {
        [RecvMsg.YOUR_TEAM]: 'Ваша команда: {0}',
        [RecvMsg.ASK_TRUMP]: 'Выберите козырь',
        [RecvMsg.PLAYER_ASKS_TRUMP]: '{0} назначает козырь',
        [RecvMsg.TRUMP_IS]: 'Назначен козырь: {0}',
        [RecvMsg.YOUR_PARTIE_SCORE]: 'Ваша команда набрала в партии {0} очков'
    });
    
    const table = new CardTable(4, document.getElementById('hand'), document.getElementById('table'));
    ctrl.lockOnTurns(conn, table);
    conn.on(RecvMsg.DEAL, table.deal.bind(table));
    conn.on(RecvMsg.PLAYER_PUTS_CARD, ({player, card}) => table.playerPutsCard(player, card));
    conn.on(RecvMsg.ASK_TRUMP, () => table.askTrump(trump => conn.send(SendMsg.DETERM_TRUMP, trump)));
    table.onPutCard(card => {
        conn.send(SendMsg.PUT_CARD, card);
        table.lock();
    });
});
})();
