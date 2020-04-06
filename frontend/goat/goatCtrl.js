(() => {
const controls = document.querySelector('#controls');
const ctrl = new GameController(GameController.createDefaultElems(controls));

ctrl.onPlay(conn => {
    ctrl.messageOn(conn, RecvMsg.YOUR_TEAM, 'Ваша команда: {0}');
    ctrl.messageOn(conn, RecvMsg.ASK_TRUMP, 'Выберите козырь');
    ctrl.messageOn(conn, RecvMsg.HE_ASKS_TRUMP, '{0} назначает козырь');
    ctrl.messageOn(conn, RecvMsg.TRUMP_IS, 'Назначен козырь: {0}');
    
    const table = new CardTable;
    conn.on(RecvMsg.DEAL, table.deal.bind(table));
    ctrl.lockOnTurns(conn, table);
    
    table.onPutCard(card => {
        conn.send(SendMsg.PUT_CARD, card);
        table.lock();
    });
});
})();
