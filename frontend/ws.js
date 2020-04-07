class WebsocketConn {
    static RecvMsg = Object.freeze({
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
        this.send(WebsocketConn.SendMsg.CONNECT, name);
        return this;
    }
    
    close() {
        this._ws.close();
    }

    on(msgType, sub) {
        if (!this._subscribers[msgType]) {
            this._subscribers[msgType] = [];
        }
        this._subscribers[msgType].push(sub);
    }

    onClose(handler) {
        this._ws.addEventListener('close', handler);
    }

    send(type, payload = undefined) {
        const send = () => this._ws.send(JSON.stringify({type, payload}));

        if (this._ws.readyState === 0) { // connecting
            this._ws.addEventListener('open', send);
        } else {
            send();
        }
    }
    
    preparePayload(preparers) {
        Object.assign(this._payloadPreparers, preparers);
    }

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
