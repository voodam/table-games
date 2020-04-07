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
    static fromDict({rank, suit}) { return new Card(rank, suit); }
    
    constructor(rank, suit) {
        this._rank = rank;
        this._suit = suit;
        
    }

    //get rank() { return this._rank; }
    //get suit() { return this._suit; }
    toString() { return this._rank . this._suit; }
    toJSON() { return {rank: this._rank, suit: this._suit}; }
}

class Player {
    constructor(name) {
        this._name = name;
    }
    
    //get name() { return this._name; }
    toJSON() { return this._name; }
}
