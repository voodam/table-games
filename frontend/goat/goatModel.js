const RecvMsg = Object.freeze({
    YOUR_TEAM: 'yourTeam',
    DEAL: 'deal',
    PLAYER_PUTS_CARD: 'playerPutsCard',
    ASK_TRUMP: 'askTrump',
    PLAYER_ASKS_TRUMP: 'playerAsksTrump',
    TRUMP_IS: 'trumpIs',
    TRICK_WINNER_IS: 'trickWinnerIs',
    YOUR_PARTIE_SCORE: 'yourPartieScore'
});

const SendMsg = Object.freeze({
    DETERM_TRUMP: 'determTrump',
    PUT_CARD: 'putCard'
});

class CardTable extends GameTable {
    constructor(playersNumber, handContainer, tableContainer) {
        super();
        this._playersNumber = playersNumber;
        this._hand = handContainer;
        this._table = tableContainer;
        this._listenBrowserEvents();
        this._trumpSelecting = false;
    }
    
    deal(hand) {
        appendChildren(this._hand, hand.map(card => card.createImage()), true);
    }
    
    playerPutsCard(card) {
        this._table.appendChild(card.createImage());
    }
    
    clearTable() {
        if (!this._trumpSelecting && this._table.childElementCount >= this._playersNumber) {
            clearElement(this._table);
        }
    }
    
    clear() {
        super.clear();
        clearElement(this._hand);
        this.clearTable();
    }
    
    rollbackTurn() {
        super.rollbackTurn();
        const lastCard = this._table.lastChild;
        lastCard.remove();
        this._hand.appendChild(lastCard);
    }
    
    askTrump(handler) {
        const suitCards = ['clubs', 'diamonds', 'hearts', 'spades'].map(suit => new Card('ace', suit));
        appendChildren(this._table, suitCards.map(card => card.createImage()), true);
        this._trumpSelecting = true;
        
        this._table.addEventListener('dblclick', ({target}) => {
            const card = Card.fromImage(target);
            handler(card.suit);
            this._trumpSelecting = false;
            this.clearTable();
        }, {once: true});
    }
    
    onPutCard(handler) { this._onPutCard = handler; }
    _onPutCard() {}
    
    _lockingElement() { return this._hand; }
    _listenBrowserEvents() { this._hand.addEventListener('dblclick', this._cardClickHandler); }
    _stopListenBrowserEvents() { this._hand.removeEventListener('dblclick', this._cardClickHandler); }
    _cardClickHandler = ({target}) => {
        const card = Card.fromImage(target);
        this.playerPutsCard(card);
        target.remove();
        this._onPutCard(card);
    }
}

class Card {
    static fromDict({rank, suit}) { return new Card(rank, suit); }
    
    static fromImage(image) {
        console.assert(image instanceof Image);
        return Card.fromDict(image.dataset);
    }
    
    constructor(rank, suit) {
        this._rank = rank;
        this._suit = suit;
        
    }
    
    createImage() {
        const path = `img/${this._suit}_${this._rank}.png`;
        const image = new Image();
        image.dataset.rank = this._rank;
        image.dataset.suit = this._suit;
        image.classList.add('game-card');
        image.src = path;
        return image;
    }

    get rank() { return this._rank; }
    get suit() { return this._suit; }
    toString() { return this._rank . this._suit; }
    toJSON() { return [this._rank, this._suit]; }
}
