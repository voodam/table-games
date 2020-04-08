class GameTable {
    rollbackTurn() { this.unlock(); }
    _lockingElement() { throw new NotImplemented; }
    _listenBrowserEvents() { throw new NotImplemented; }
    _stopListenBrowserEvents() { throw new NotImplemented; }
    
    constructor() {
        this._locked = false;
    }
    
    lock() {
        this._stopListenBrowserEvents();
        this._lockingElement().classList.add('game-element-locked');
        this._locked = true;
    }
    
    unlock() {
        if (!this._locked) {
            return false;
        }
        this._listenBrowserEvents();
        this._lockingElement().classList.remove('game-element-locked');
        this._locked = false;
        return true;
    }
    
    clear() {
        this._lockingElement().classList.remove('game-element-locked');
        this._stopListenBrowserEvents();
    }
}
