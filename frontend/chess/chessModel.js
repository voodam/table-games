const RecvMsg = Object.freeze({
    CREATE_PIECES: 'createPieces',
    MOVE_PIECES: 'movePieces',
    REMOVE_PIECE: 'removePiece'
});

const SendMsg = Object.freeze({
    MOVE_PIECE: 'movePiece'
});

class Board extends GameTable {
    /**
     * @param {HTMLTableElement} table Table 8 x 8 with cells (td-s) images in which can be set through 'background-image' property
     */
    constructor(table, colors = {moveStart: '#7facff', highlight: '#136b26'}) {
        super();
        
        const isTbl8x8 = () => this._getCellByCoords(new Coords('A', 8));
        this._table = table;
        if (!isTbl8x8()) throw new BoardException('Table has less then 8 rows or 8 columns');

        this._colors = colors;
        const a1 = this._getCellByCoords(new Coords('A', 1));
        const a2 = this._getCellByCoords(new Coords('A', 2));
        this._colors.cell = {
            [Color.WHITE]: Style.getComputed(a2, 'backgroundColor'),
            [Color.BLACK]: Style.getComputed(a1, 'backgroundColor')
        };
        /**
         * Coordinates of the current moving piece (not empty when the move transaction started).
         *
         * @type {?Coords} Undefined if move transaction isn't started
         */
        this._curMoveFrom;
        /**
         * @type {Coords[]}
         */
        this._highlightedCoords = [];
        this._listenBrowserEvents();
        // fix browser events
        // dragging sometimes occurs for some reason and 'mouseup' event will not be fired; so prevent it
        this._table.addEventListener('dragstart', (event) => event.preventDefault());
    }

    movePiece(from, to) {
        if (!this._existsPieceOn(from)) {
            throw new BoardException(`Piece not found on coords ${from}`);
        }

        const fromCell = this._getCellByCoords(from);
        this._setPiece(fromCell.style.backgroundImage, to);
        this._removePiece(fromCell);
    }
    
    createPiece(piece, coords) {
        this._setPiece(piece.getImagePath(), coords);
    }

    removePiece(coords) {
        const cell = this._getCellByCoords(coords);
        this._removePiece(cell);
    }

    highlightMove(from, to) {
        this._setColor(from, this._colors.highlight);
        this._setColor(to, this._colors.highlight);
        this._highlightedCoords.push(from);
        this._highlightedCoords.push(to);
    }

    clearHighlight() {
        this._highlightedCoords.forEach(this._resetColor.bind(this));
        this._highlightedCoords = [];
    }

    onMove(hdl) {
        this._onMove = hdl;
    }

    lock() {
        super.lock();
        this._table.classList.add('locked');
    }

    unlock() {
        if (!super.unlock()) {
            return false;
        }
        
        this._table.classList.remove('locked');
        return true;
    }

    clear() {
        this._table.classList.remove('locked');
        this._table.querySelectorAll('td').forEach(this._removePiece.bind(this));
        this._stopListenBrowserEvents();
        this.clearHighlight();
    }

    _listenBrowserEvents() {
        this._table.addEventListener('mousedown', this._mousedownHdl);
        this._table.addEventListener('mouseup', this._mouseupHdl);
    }

    _stopListenBrowserEvents() {        
        this._table.removeEventListener('mousedown', this._mousedownHdl);
        this._table.removeEventListener('mouseup', this._mouseupHdl);
    }

    _mousedownHdl = ({target}) => {
        const coords = this._getCoordsByCell(target);

        // piece found, move transaction is started
        if (!this._curMoveFrom && this._existsPieceOn(coords)) {
            this._curMoveFrom = coords;
            Style.replace(target, 'backgroundColor', this._colors.moveStart);
            return;
        }

        // double click on the same cell, move is aborted
        if (this._curMoveFrom && this._curMoveFrom.eq(coords)) {
            this._finishMove();
        }
    };

    _mouseupHdl = ({target}) => {
        const to = this._getCoordsByCell(target);
        // click on an empty sell or mouseup was fired on the move first cell
        if (!this._curMoveFrom || this._curMoveFrom.eq(to)) {
            return;
        }

        // move is commited
        this.movePiece(this._curMoveFrom, to);
        this._onMove(this._curMoveFrom, to);
        this._finishMove();
    };
    
    /**
     * Helper method for mouse event handlers.
     */
    _finishMove() {
        const cell = this._getCellByCoords(this._curMoveFrom);
        Style.returnBack(cell, 'backgroundColor');
        delete this._curMoveFrom;
    }

    _onMove() {}

    _setPiece(bgImage, coords) {
        const cell = this._getCellByCoords(coords);
        cell.style.backgroundImage = bgImage;
    }

    _removePiece(cell) {
        cell.style.removeProperty('background-image');
    }

    /**
     * @returns {?Piece} undefined if cell with given coordinates is empty
     */
    _existsPieceOn(coords) {
        const cell = this._getCellByCoords(coords);
        return !!cell.style.backgroundImage;
    }

    _resetColor(coords) {
        const isBlack = cell => (cell.cellIndex + cell.parentElement.rowIndex) % 2;

        const cell = this._getCellByCoords(coords);        
        const color = isBlack(cell) ? Color.BLACK : Color.WHITE;
        this._setColor(coords, this._colors.cell[color]);
    }

    _setColor(coords, color) {
        const cell = this._getCellByCoords(coords);
        cell.style.backgroundColor = color;
    }

    _getCellByCoords(coords) {
        const coordsToSelector = ({letter, number}) => {
            const trIndex = 9 - number;
            const tdIndex = letter.charCodeAt(0) - 64;
            return `tr:nth-child(${trIndex}) td:nth-child(${tdIndex})`;
        };

        const selector = coordsToSelector(coords);
        const cell = this._table.querySelector(selector);
        console.assert(cell instanceof HTMLTableCellElement);
        return cell;
    }

    _getCoordsByCell(cell) {
        const letter = String.fromCharCode(cell.cellIndex + 65);
        const number = 8 - cell.parentElement.rowIndex;
        return new Coords(letter, number);
    }
}

class Piece {
    static Type = Object.freeze({BISHOP: 'bishop', HORSE: 'horse', KING: 'king', PAWN: 'pawn', QUEEN: 'queen', ROOK: 'rook'});

    static fromDict({type, color}) {
        return new Piece(type, color);
    }

    /**
     * @param {Piece.Type} type
     * @param {Color} color
     */
    constructor(type, color) {
        this._type = type;
        this._color = color;
    }
    
    getImagePath() {
        return `url(img/${this._color}_${this._type}.svg)`;
    }
}

class Coords {
    static fromDict({letter, number}) {
        return new Coords(letter, number);
    }

    constructor(letter, number) {
        this._letter = letter;
        this._number = number;
    }

    get letter() {  
        return this._letter;
    }

    get number() {
        return this._number;
    }

    eq({letter, number}) {
        return this.letter === letter && this.number === number;
    }

    toString() {
        return `${this.letter}-${this.number}`;
    }

    toJSON() {
        return {
            letter: this.letter,
            number: this.number
        };
    }
}

const Color = Object.freeze({WHITE: 'white', BLACK: 'black'});

class BoardException extends Error {}
