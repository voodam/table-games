(() => {
Debug.init();

const ctrlFactory = () => {
    const template = document.querySelector('.controller-template .controller');
    const ctrlWrapper = template.cloneNode(true);
    document.querySelector('.controllers-container').appendChild(ctrlWrapper);
    
    const controls = ctrlWrapper.querySelector('.controls');
    const ctrl = new GameController(new PromptInputManager, GameController.createDefaultControls(controls));
    return [ctrl, ctrlWrapper];
};

const mpCtrl = new MultiplayerGameController({
    addPlayer: document.querySelector('.add-player')
}, ctrlFactory);
mpCtrl.displayOn(RecvMsg.ASK_TRUMP);

mpCtrl.onPlay((conn, ctrl, ctrlWrapper) => {
    const cardPreparer = argsArrayToRest(_new(Card));
    conn.preparePayload({
        [RecvMsg.DEAL]: hand => hand.map(cardPreparer),
        [RecvMsg.PLAYER_PUTS_CARD]: cardPreparer
    });
    ctrl.messagesOn(conn, {
        [RecvMsg.YOUR_TEAM]: 'Ваша команда: {0}',
        [RecvMsg.ASK_TRUMP]: 'Выберите козырь, {0}',
        [RecvMsg.PLAYER_DETERMS_TRUMP]: '{0} назначает козырь',
        [RecvMsg.TRICK_WINNER_IS]: '{0} забирает взятку в {1} очков', 
        [RecvMsg.YOUR_PARTIE_SCORE]: 'Ваша команда набрала {0} очков'
    });
    
    const table = new CardTable(4, ctrlWrapper.querySelector('.hand'), ctrlWrapper.querySelector('.table'), ctrlWrapper.querySelector('.trump'));
    GameController.initLocking(conn, table);
    conn.on(RecvMsg.DEAL, table.deal.bind(table));
    conn.on(RecvMsg.PLAYER_PUTS_CARD, table.playerPutsCard.bind(table));
    
    const hideHand = () => {
        if (mpCtrl.playersNumber > 1 && !Debug.enabled()) table.hideHand();
    };
    conn.on(WebsocketConn.RecvMsg.YOUR_TURN, hideHand);
    conn.on(RecvMsg.TRUMP_IS, table.displayTrump.bind(table));
    conn.on(RecvMsg.ASK_TRUMP, () => {
        hideHand();
        table.askTrump(trump => conn.send(SendMsg.DETERMINE_TRUMP, trump));
    });
    table.onPutCard(card => {
        conn.send(SendMsg.PUT_CARD, card);
        table.lock();
    });
});
})();
