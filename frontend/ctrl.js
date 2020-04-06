class GameController {
    static DEFAULT_MSGS = {
        waitPlayers: 'Ожидайте остальных игроков',
        gameAborted: 'Игра прервана: слишком много игроков, игрок вышел или соединение оборвалось',
        sureAbort: 'Вы уверены?',
        enterUrl: 'Введите адрес сервера',
        enterName: 'Введите имя',
        yourTurn: 'Ваш ход',
        turnOf: 'Ходит {0}',
        wrongTurn: 'Неверный ход: {0}',
        winnerIs: 'Победил(и) {0}!'
    };

    static createDefaultElems(parent, withNameInput = true) {
        const serverPath = location.pathname.slice(0, -1);
        const elems = createElemsFromStr(
            `<div class="inputs">
                <div class="input-elements">
                    <input id="server-url" placeholder="Адрес сервера" value="ws://192.168.1.36:8080${serverPath}">
                    <input id="name" placeholder="Введите имя">
                </div>
                <button id="play">Играть!</button>
                <button id="abort" disabled="true">Закончить</button>
            </div>
            <div class="info">
                <div id="messages"></div>
                <div><div>Игровой счет</div><div id="score">0</div></div>
            </div>`);
        
        for (const elem of elems) {
            parent.appendChild(elem);
        }
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
        this._messagesContainer = elements.messages;
        this._nameInput = elements.name;
        this._scoreStatus = elements.score;
        this._messages = messages;
    }

    onPlay(hdl) {
        //beforeUnload();

        this._play.addEventListener('click', () => {
            const serverUrl = this._serverUrlInput.value.trim();
            if (!serverUrl) {
                this.message(this._messages.enterUrl);
                return;
            }

            let name;
            if (this._nameInput) {
                name = this._nameInput.value.trim();
                if (!name) {
                    this.message(this._messages.enterName);
                    return;
                }
            }

            this._toggleButtons();
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

            this.messageOn(conn, WebsocketConn.RecvMsg.WAIT_PLAYERS, this._messages.waitPlayers);
            this.messageOn(conn, WebsocketConn.RecvMsg.YOUR_TURN, this._messages.yourTurn);
            this.messageOn(conn, WebsocketConn.RecvMsg.TURN_OF, this._messages.turnOf);
            this.messageOn(conn, WebsocketConn.RecvMsg.WRONG_TURN, this._messages.wrongTurn);
            this.messageOn(conn, WebsocketConn.RecvMsg.WINNER_IS, this._messages.winnerIs);
            conn.on(WebsocketConn.RecvMsg.GAME_SCORE, (scores) => {
                const elements = Object.keys(scores).map(name => createElement(`${name}: ${scores[name]}`));
                this._scoreStatus.textContent = '';
                for (const elem of elements) {
                    this._scoreStatus.appendChild(elem);
                }
            });
            conn.onClose(() => {
                this.message(this._messages.gameAborted);
                this._toggleButtons();
            });

            hdl(conn);
        });
    }

    message(msg) {
        this._messagesContainer.prepend(createElement(msg));
    }

    messageOn(conn, type, msg) {
        conn.on( type, payload => this.message(format(msg, payload)) );
    }
    
    /**
     * @param {WebsocketConn} conn
     * @param {GameTable} table
     */
    lockOnTurns(conn, table) {
        conn.on(WebsocketConn.RecvMsg.YOUR_TURN, table.unlock.bind(table));
        conn.on(WebsocketConn.RecvMsg.TURN_OF, table.lock.bind(table));
    }
    
    _toggleButtons() {
        [this._play, this._abort].map(toggleDisabled);
    }
}
