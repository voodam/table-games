class WebsocketConn {
    static RecvMsg = Object.freeze({
        YOUR_TURN: 'yourTurn',
        WAIT_PLAYERS: 'waitPlayers',
        TURN_OF: 'turnOf'
    });

    static SendMsg = Object.freeze({
        CONNECT: 'connect'
    });

    constructor(url) {
        this._url = url;
        this._subscribers = [];
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

    on(type, sub) {
        if (!this._subscribers[type]) {
            this._subscribers[type] = [];
        }
        this._subscribers[type].push(sub);
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

    _messageListener({data}) {
        console.log(`recv message: ${data}`);
        const message = JSON.parse(data);
        if (typeof message.type !== 'string') throw new Error(`Message ${data} has not type`);

        for (const sub of this._subscribers[message.type] || []) {
            sub(message.payload, event);
        }
    }
}

