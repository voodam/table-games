class GameTable {
    constructor() {
        this._locked = false;
    }
    
    lock() {
        this._stopListenBrowserEvents();
        this._table.classList.add('game-element-locked');
        this._locked = true;
    }
    
    unlock() {
        if (!this._locked) {
            return false;
        }
        this._listenBrowserEvents();
        this._table.classList.remove('game-element-locked');
        this._locked = false;
        return true;
    }
    
    clear() {
        this._table.classList.remove('game-element-locked');
        this._stopListenBrowserEvents();
    }
    
    _lockingElement() { throw new NotImplemented; }
    _listenBrowserEvents() { throw new NotImplemented; }
    _stopListenBrowserEvents() { throw new NotImplemented; }
}
