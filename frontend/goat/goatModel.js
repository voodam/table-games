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
    constructor(playersNumber, handContainer, tableContainer, trumpContainer) {
        super();
        this._playersNumber = playersNumber;
        this._hand = handContainer;
        this._table = tableContainer;
        this._trump = trumpContainer;
        this._listenBrowserEvents();
        this._hiddenHand = false;
        this._trumpSelectedAndNoTurns = false;
    }
    
    deal(hand) {
        appendChildren(this._hand, hand.map(card => card.createImage()), true);
        clearElement(this._trump);
    }
    
    playerPutsCard(player, card) {
        this._clearTable();
        this._table.appendChild(card.createImage(player));
    }
    
    hideHand() {
        if (this._hiddenHand || !this.haveCards()) {
            return;
        }
        if (this._trumpSelectedAndNoTurns) {
            this._trumpSelectedAndNoTurns = false;
            return;
        }
        
        this._forEachCard(card => card.src = 'img/back_green.png');
        this._stopListenBrowserEvents();
        this._hiddenHand = true;
        
        this._hand.addEventListener('click', () => {
            if (this._hiddenHand) {
                this._forEachCard(card => card.src = card.dataset.src);
                this._hiddenHand = false;
            }

            if (!this._locked) {
                this._listenBrowserEvents();
            }
        }, {once: true});
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
        setTimeout(() => {
            const suitCards = ['clubs', 'diamonds', 'hearts', 'spades'].map(suit => new Card('ace', suit));
            appendChildren(this._table, suitCards.map(card => card.createImage('Выберите козырь')), true);

            listenOnce(this._table, 'click', this._createImageHandler(({target}) => {
                const card = Card.fromImage(target);
                handler(card.suit);
                this._trumpSelectedAndNoTurns = true;
                this._clearTable();
            }));
        }, 1000);
    }
    
    displayTrump(trump) {
        assignElement(this._trump, new Card('ace', trump).createImage('Козырь'));
    }
    
    haveCards() {
        return this._hand.childElementCount > 0;
    }
    
    _clearTable() {
        if (this._table.childElementCount >= this._playersNumber) {
            clearElement(this._table);
        }
    }
    
    onPutCard(handler) { this._onPutCard = handler; }
    _onPutCard() {}
    
    _forEachCard(handler) {
        for (const cardWrapper of this._hand.children) {
            handler(cardWrapper.firstChild);
        }
    }
    
    _removeHandCard(cardImage) {
        cardImage.parentNode.remove();
    }
    
    _cardClickHandler = this._createImageHandler(({target}) => {
        const card = Card.fromImage(target);
        this.playerPutsCard('Вы', card);
        this._removeHandCard(target);
        this._onPutCard(card);
    });
    
    _listenBrowserEvents() {
        if (!this._hiddenHand) {
            this._hand.addEventListener('click', this._cardClickHandler); 
        }
    }
    
    _createImageHandler(handler) {
        return event => event.target instanceof Image ? handler(event) : false;
    }
    
    _stopListenBrowserEvents() { this._hand.removeEventListener('click', this._cardClickHandler); }
    _lockingElement() { return this._hand; }
}

class Card {
    static fromDict({rank, suit}) { return new Card(rank, suit); }
    
    static fromImage(image) {
        console.assert(image.dataset.rank);
        console.assert(image.dataset.suit);
        return Card.fromDict(image.dataset);
    }
    
    constructor(rank, suit) {
        this._rank = rank;
        this._suit = suit;
    }
    
    createImage(tooltipMsg = undefined) {
        const path = `img/${this._suit}_${this._rank}.png`;
        const image = new Image;
        image.dataset.rank = this._rank;
        image.dataset.suit = this._suit;
        image.classList.add('game-card');
        image.src = path;
        image.dataset.src = path;
        
        const wrapper = createElement('div', '', ['game-card-wrap', 'tooltip-object']);
        wrapper.appendChild(image);
        if (tooltipMsg) {
            const tooltip = createElement('span', tooltipMsg, 'tooltip-message');
            wrapper.appendChild(tooltip);
        }
        return wrapper;
    }

    get rank() { return this._rank; }
    get suit() { return this._suit; }
    toString() { return this._rank . this._suit; }
    toJSON() { return [this._rank, this._suit]; }
}
