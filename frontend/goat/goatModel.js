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
    constructor(handContainer, tableContainer) {
        super();
        this._handContainer = handContainer;
        this._tableContainer = tableContainer;
    }
    
    deal(hand) {
        appendChildren(this._handContainer, hand.map(card => card.createImage()));
    }
    onPutCard(handler) {}
    playerPutsCard(player, card) {}
    askTrump(handler) {}
    _stopListenBrowserEvents() {}
    _listenBrowserEvents() {}
}

class Card {
    static fromDict({rank, suit}) { return new Card(rank, suit); }
    
    constructor(rank, suit) {
        this._rank = rank;
        this._suit = suit;
        
    }
    
    createImage() {
        const path = `img/${this._suit}_${this._rank}`;
        const image = new Image();
        image.src = path;
        return image;
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
