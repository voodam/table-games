const RecvMsg = Object.freeze({
    YOUR_TEAM: 'yourTeam',
    DEAL: 'deal',
    HE_PUTS_CARD: 'hePutsCard', //*
    ASK_TRUMP: 'askTrump', //*
    HE_ASKS_TRUMP: 'heAsksTrump',
    TRUMP_IS: 'trumpIs',
    YOUR_PARTIE_SCORE: 'yourPartieScore' //*
});

const SendMsg = Object.freeze({
    DETERM_TRUMP: 'determTrump', //*
    PUT_CARD: 'putCard' //*
});

class CardTable extends GameTable {
    constructor() {
        super();    
    }
    
    deal() {}
    _stopListenBrowserEvents() {}
    _listenBrowserEvents() {}
}

class Card {
    toJSON() {}
}
