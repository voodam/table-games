const RecvMsg = Object.freeze({
    YOUR_TEAM: 'yourTeam',
    DEAL: 'deal',
    PLAYER_PUTS_CARD: 'playerPutsCard',
    ASK_TRUMP: 'askTrump',
    PLAYER_DETERMS_TRUMP: 'playerDetermsTrump',
    TRUMP_IS: 'trumpIs',
    TRICK_WINNER_IS: 'trickWinnerIs',
    YOUR_PARTIE_SCORE: 'yourPartieScore'
});

const SendMsg = Object.freeze({
    DETERMINE_TRUMP: 'determineTrump',
    PUT_CARD: 'putCard'
});

class CardTable extends GameTable {
    constructor(playersNumber, handContainer, tableContainer, trumpImage) {
        super();
        this._playersNumber = playersNumber;
        this._hand = handContainer;
        this._table = tableContainer;
        this._trump = trumpImage;
        this._listenBrowserEvents();
        this._trumpSelecting = false;
        this._hiddenHand = false;
    }
    
    deal(hand) {
        appendChildren(this._hand, hand.map(card => card.createImage()), true);
    }
    
    playerPutsCard(card) {
        this._table.appendChild(card.createImage());
    }
    
    hideHand() {
        for (const cardImage of this._hand.children) {
            cardImage.dataset.src = cardImage.src;
            cardImage.src = 'img/back_green.png';
        }
        
        this._stopListenBrowserEvents();
        const handler = () => {
            for (const cardImage of this._hand.children) {
                cardImage.src = cardImage.dataset.src;
            }
            if (this._locked) {
                return false;
            }
            this._hiddenHand = false;
            this._listenBrowserEvents();
        };
        listenOnce(this._hand, 'click', handler);
        this._hiddenHand = true;
    }
    
    clearTable() {
        if (!this._trumpSelecting && this._table.childElementCount >= this._playersNumber) {
            clearElement(this._table);
        }
    }
    
    clear() {
        super.clear();
        clearElement(this._hand);
        clearElement(this._table);
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
        
        this._table.addEventListener('click', ({target}) => {
            const card = Card.fromImage(target);
            handler(card.suit);
            this._trumpSelecting = false;
            this.clearTable();
        }, {once: true});
    }
    
    displayTrump(trump) {
        const card = new Card('ace', trump);
        this._trump.src = card.createImage().src;
    }
    
    clearTrump() {
        this._trump.src = '';
    }
    
    onPutCard(handler) { this._onPutCard = handler; }
    _onPutCard() {}
    
    _cardClickHandler = ({target}) => {
        const card = Card.fromImage(target);
        this.playerPutsCard(card);
        target.remove();
        this._onPutCard(card);
    }
    
    _listenBrowserEvents() {
        if (!this._hiddenHand) {
            this._hand.addEventListener('click', this._cardClickHandler); 
        }
    }
    
    _stopListenBrowserEvents() { this._hand.removeEventListener('click', this._cardClickHandler); }
    _lockingElement() { return this._hand; }
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
