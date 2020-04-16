class WebsocketConn {
    static RecvMsg = Object.freeze({
        START_GAME: 'startGame',
        YOUR_TEAM: 'yourTeam',
        YOUR_TURN: 'yourTurn',
        WAIT_PLAYERS: 'waitPlayers',
        TURN_OF: 'turnOf',
        WRONG_TURN: 'wrongTurn',
        WINNER_IS: 'winnerIs',
        GAME_SCORE: 'gameScore'
    });

    static SendMsg = Object.freeze({
        CONNECT: 'connect'
    });

    constructor(url) {
        this._url = url;
        this._subscribers = [];
        this._payloadPreparers = {};
    }

    connect(name = null) {
        console.log(`connecting to ${this._url}`);
        this._ws = new WebSocket(this._url);
        this._ws.addEventListener('message', this._messageListener.bind(this));
        if (Debug.enabled()) {
            if (!window.conns) window.conns = [];
            window.conns.push(this);
        }
        this.send(WebsocketConn.SendMsg.CONNECT, name);
        return this;
    }

    send(type, payload = undefined) {
        const message = JSON.stringify({type, payload});
        console.log(`send message: ${message}`);
        const send = () => this._ws.send(message);

        if (this._ws.readyState === 0) { // connecting
            this._ws.addEventListener('open', send);
        } else {
            send();
        }
    }
    
    on(msgTypes, sub) {
        for (const msgType of toArray(msgTypes)) {
            if (!this._subscribers[msgType]) {
                this._subscribers[msgType] = [];
            }
            this._subscribers[msgType].push(sub);
        }
    }    
    
    preparePayload(preparers) { Object.assign(this._payloadPreparers, preparers); }
    onOpen(handler) { this._ws.addEventListener('open', handler); }
    onClose(handler) { this._ws.addEventListener('close', handler); }
    close() { this._ws.close(); }

    _messageListener({data}) {
        console.log(`recv message: ${data}`);
        const message = JSON.parse(data);
        if (typeof message.type !== 'string') throw new Error(`Message ${data} has not type`);
        
        let {type, payload} = message;
        payload = (this._payloadPreparers[type] || id)(payload);
        for (const sub of this._subscribers[type] || []) {
            sub(payload, event);
        }
    }
}
