class GameTable {
    constructor() {
        this._locked = false;
    }
    
    lock() {
        this._stopListenBrowserEvents();
        this._locked = true;
    }
    
    unlock() {
        if (!this._locked) {
            return false;
        }
        this._listenBrowserEvents();
        this._locked = false;
        return true;
    }
    
    _stopListenBrowserEvents() { throw new NotImplemented; }
    _listenBrowserEvents() { throw new NotImplemented; }
}
