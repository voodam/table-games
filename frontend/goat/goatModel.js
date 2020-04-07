const RecvMsg = Object.freeze({
    YOUR_TEAM: 'yourTeam',
    DEAL: 'deal',
    PLAYER_PUTS_CARD: 'playerPutsCard',
    ASK_TRUMP: 'askTrump',
    PLAYER_ASKS_TRUMP: 'playerAsksTrump',
    TRUMP_IS: 'trumpIs',
    YOUR_PARTIE_SCORE: 'yourPartieScore'
});

const SendMsg = Object.freeze({
    DETERM_TRUMP: 'determTrump',
    PUT_CARD: 'putCard'
});

class CardTable extends GameTable {
    constructor() {
        super();    
    }
    
    deal() {}
    onPutCard() {}
    askTrump() {}
    playerPutsCard() {}
    _stopListenBrowserEvents() {}
    _listenBrowserEvents() {}
}

class Card {
    static fromDict() {}
    toJSON() {}
}

class Player {
    static fromDict() {}
}
