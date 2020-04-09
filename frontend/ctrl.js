class GameController {
    static DEFAULT_MSGS = {
        waitPlayers: 'Ожидайте остальных игроков',
        gameAborted: 'Игра прервана',
        sureAbort: 'Вы уверены?',
        enterUrl: 'Введите адрес сервера',
        enterName: 'Введите имя',
        yourTurn: 'Ваш ход',
        turnOf: 'Ходит {0}',
        wrongTurn: '{0}',
        winnerIs: 'Победил(и) {0}!'
    };

    static createDefaultElems(parent, withNameInput = true) {
        const serverPath = location.pathname.slice(0, -1);
        const elements = createElemsFromStr(
            `<div class="inputs">
                <div class="input-elements">
                    <input id="server-url" placeholder="Адрес сервера" value="ws://91.191.245.9:8080${serverPath}">
                    <input id="name" placeholder="Введите имя">
                </div>
                <div class="buttons">
                    <button id="play">Играть!</button>
                    <button id="abort" disabled="true">Закончить</button>
                </div>
            </div>
            <div class="info">
                <div><div>Игровой счет</div><div id="score"></div></div>
                <div id="messages"></div>
            </div>`);
        
        appendChildren(parent, elements);
        if (!withNameInput) {
            parent.querySelector('#name').remove();
        }

        return {
            play: parent.querySelector('#play'),
            abort: parent.querySelector('#abort'),
            serverUrl: parent.querySelector('#server-url'),
            messages: parent.querySelector('#messages'),
            name: withNameInput ? parent.querySelector('#name') : undefined,
            score: parent.querySelector('#score')
        };
    }

    constructor(elements, messages = GameController.DEFAULT_MSGS) {
        this._play = elements.play;
        this._abort = elements.abort;
        this._serverUrlInput = elements.serverUrl;
        this._nameInput = elements.name;
        this._messagesContainer = elements.messages;
        this._scoreStatus = elements.score;
        this._messages = messages;
    }

    onPlay(hdl) {
        if (!Debug.enabled()) {
            beforeUnload();
        }

        this._play.addEventListener('click', () => {
            const serverUrl = this._serverUrlInput.value.trim();
            if (!serverUrl) {
                this.message(this._messages.enterUrl);
                return;
            }
            
            this._toggleControls();
            const name = this._nameInput && this._nameInput.value.trim() || null;
            const conn = new WebsocketConn(serverUrl);
            conn.connect(name);

            const abortHdl = () => {
                const sure = confirm(this._messages.sureAbort);
                if (!sure) {
                    return;
                }

                conn.close();
                this._abort.removeEventListener('click', abortHdl);
            };
            this._abort.addEventListener('click', abortHdl);

            this.messagesOn(conn, {
                [WebsocketConn.RecvMsg.WAIT_PLAYERS]: this._messages.waitPlayers,
                [WebsocketConn.RecvMsg.YOUR_TURN]: this._messages.yourTurn,
                [WebsocketConn.RecvMsg.TURN_OF]: this._messages.turnOf,
                [WebsocketConn.RecvMsg.WRONG_TURN]: this._messages.wrongTurn,
                [WebsocketConn.RecvMsg.WINNER_IS]: this._messages.winnerIs
            });
            conn.on(WebsocketConn.RecvMsg.GAME_SCORE, (score) => {
                const elements = Object.keys(score).map(name => createElement(`${name}: ${score[name]}`));
                appendChildren(this._scoreStatus, elements, true);
            });
            conn.onClose(() => {
                this.message(this._messages.gameAborted);
                this._toggleControls();
            });

            hdl(conn);
        });
    }

    message(msg) {
        this._messagesContainer.prepend(createElement(msg));
    }

    messagesOn(conn, messages) {
        for (const [type, msg] of Object.entries(messages)) {
            conn.on(type, payload => {
                if (!Array.isArray(payload)) {
                    payload = [payload];
                }
                this.message(format(msg, ...payload));
            });
        }
    }
    
    /**
     * @param {WebsocketConn} conn
     * @param {GameTable} table
     */
    initLocking(conn, table) {
        conn.on(WebsocketConn.RecvMsg.YOUR_TURN, table.unlock.bind(table));
        conn.on(WebsocketConn.RecvMsg.TURN_OF, table.lock.bind(table));
        conn.on(WebsocketConn.RecvMsg.WRONG_TURN, table.rollbackTurn.bind(table));
        conn.onOpen(table.lock.bind(table));
        conn.onClose(table.clear.bind(table));
    }
    
    _toggleControls() {
        [this._play, this._abort].map(toggleDisabled);
        [this._serverUrlInput, this._nameInput].map(toggleDisplay);
    }
}
