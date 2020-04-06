class Board {
    _getCellAndCoords(cellOrCoords) {
        const isCell = cellOrCoords instanceof HTMLTableCellElement;
        console.assert(isCell || cellOrCoords instanceof Coords);

        if (isCell) {
            const cell = cellOrCoords;
            return [cell, this._getCoordsByCell(cell)];
        }

        const coords = cellOrCoords;
        return [this._getCellByCoords(coords), coords];
    }

    _forEachCoord(cbk) {
        this._forEachCoordLetter(letter => {
            for (let number = 1; number <= 8; number++) {
                cbk(new Coords(letter, number));
            }
        });
    }

    _forEachCoordLetter(cbk) {  
        for (let c = 'A'; c !== 'I'; c = nextChar(c)) {
            cbk(c);    
        }
    }
}
