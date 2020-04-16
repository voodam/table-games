const RecvMsg = Object.freeze({
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
        this._trumpSelecting = false;
    }
    
    deal(hand) {
        appendChildren(this._hand, hand.map(card => card.createImage()), true);
        clearElement(this._trump);
    }
    
    playerPutsCard({name, team}, card) {
        const tooltip = span(name);
        tooltip.style.backgroundColor = team;
        
        this._clearTable();
        this._table.appendChild(card.createImage(tooltip));
        if (this._isTrickFull()) {
            this._table.classList.add('full-trick');
        }
    }
    
    hideHand() {
        if (this._hiddenHand || !this.haveCards()) {
            return;
        }
        
        this._forEachHandCard(card => card.src = 'img/back_green.png');
        this._stopListenBrowserEvents();
        this._hiddenHand = true;
        
        listenOnce(this._hand, 'click', () => {
            if (this._locked && !this._trumpSelecting) {
                return false;
            }
            
            if (this._hiddenHand) {
                this._forEachHandCard(card => card.src = card.dataset.src);
                this._hiddenHand = false;
            }
            if (!this._locked) {
                this._listenBrowserEvents();
            }
        });
    }
    
    clear() {
        super.clear();
        this._clearTable(true);
        clearElement(this._hand);
        clearElement(this._trump);
    }
    
    rollbackTurn() {
        super.rollbackTurn();
        const lastCard = this._table.lastChild;
        lastCard.remove();
        this._hand.appendChild(lastCard);
        this._table.classList.remove('full-trick');
    }
    
    askTrump(handler) {
        this._trumpSelecting = true;
        setTimeout(() => {
            const suitCards = ['clubs', 'diamonds', 'hearts', 'spades'].map(suit => new Card('ace', suit));
            this._clearTable();
            appendChildren(this._table, suitCards.map(card => card.createImage(span('Выберите козырь'))));

            listenOnce(this._table, 'click', this._createImageHandler(({target}) => {
                const card = Card.fromImage(target);
                this._trumpSelecting = false;
                handler(card.suit);
                this._clearTable();
            }));
        }, 1000);
    }
    
    displayTrump(trump, player) {
        assignElement(this._trump, new Card('ace', trump).createImage(span(player)));
    }
    
    haveCards() {
        return this._hand.childElementCount > 0;
    }
    
    _clearTable(force = false) {
        if (force || this._isTrickFull()) {
            clearElement(this._table);
            this._table.classList.remove('full-trick');
        }
    }
    
    _isTrickFull() {
        return this._table.childElementCount >= this._playersNumber;
    }
    
    onPutCard(handler) { this._onPutCard = handler; }
    _onPutCard() {}
    
    _forEachHandCard(handler) {
        for (const cardWrapper of this._hand.children) {
            handler(cardWrapper.firstChild);
        }
    }
    
    _removeHandCard(cardImage) {
        cardImage.parentNode.remove();
    }
    
    _cardClickHandler = this._createImageHandler(({target}) => {
        const card = Card.fromImage(target);
        this.playerPutsCard({name: 'Вы'}, card);
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
    
    createImage(tooltip = undefined) {
        const path = `img/${this._suit}_${this._rank}.png`;
        const image = new Image;
        image.dataset.rank = this._rank;
        image.dataset.suit = this._suit;
        image.classList.add('game-card');
        image.src = path;
        image.dataset.src = path;
        
        const wrapper = div('', ['game-card-wrap']);
        wrapper.appendChild(image);
        if (tooltip) {
            tooltip.classList.add('tooltip-message');
            wrapper.classList.add('tooltip-object');
            wrapper.appendChild(tooltip);
        }
        return wrapper;
    }

    get rank() { return this._rank; }
    get suit() { return this._suit; }
    toString() { return this._rank . this._suit; }
    toJSON() { return [this._rank, this._suit]; }
}
