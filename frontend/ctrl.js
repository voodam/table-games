class GameController {
    static DEFAULT_MSGS = {
        waitPlayers: 'Ожидайте остальных игроков',
        gameAborted: 'Игра прервана',
        enterUrl: 'Введите адрес сервера',
        enterName: 'Введите имя',
        yourTurn: 'Ваш ход, {0}',
        turnOf: 'Ходит {0}',
        wrongTurn: '{0}',
        winnerIs: 'Победил(и) {0}!'
    };

    static createDefaultControls(parent) {
        const elements = createElemsFromStr(
            `<div class="info">
                <div><div>Игровой счет</div><div class="score"></div></div>
                <div class="messages"></div>
            </div>`);
        appendChildren(parent, elements);
        return {
            messages: parent.querySelector('.info .messages'),
            score: parent.querySelector('.info .score')
        };
    }
    
    /**
     * @param {WebsocketConn} conn
     * @param {GameTable} table
     */
    static initLocking(conn, table) {
        conn.on(WebsocketConn.RecvMsg.YOUR_TURN, table.unlock.bind(table));
        conn.on(WebsocketConn.RecvMsg.TURN_OF, table.lock.bind(table));
        conn.on(WebsocketConn.RecvMsg.WRONG_TURN, table.rollbackTurn.bind(table));
        conn.onOpen(table.lock.bind(table));
        conn.onClose(table.clear.bind(table));
    }

    constructor(inputManager, info, messages = GameController.DEFAULT_MSGS) {
        this._inputManager = inputManager;
        this._messagesContainer = info.messages;
        this._scoreStatus = info.score;
        this._messages = messages;
    }

    onPlay(hdl) {
        if (!Debug.enabled()) {
            beforeUnload();
        }

        this._inputManager.onCredentials((name, serverUrl) => {
            const conn = new WebsocketConn(serverUrl);
            conn.connect(name || null);
            this._inputManager.onAbort(conn.close.bind(conn));
            conn.onClose(() => {
                this.message(this._messages.gameAborted);
                this._inputManager.onClose();
            });

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
}

class InputManager {
    static DEFAULT_MSGS = {
        sureAbort: 'Вы уверены?'
    };
    
    static createDefaultControls(parent, withNameInput = true) {
        const serverPath = location.pathname.slice(0, -1);
        const elements = createElemsFromStr(
            `<div class="inputs">
                <div class="input-elements">
                    <input class="server-url" placeholder="Адрес сервера" value="ws://91.191.245.9:8080${serverPath}">
                    <input class="name" placeholder="Введите имя">
                </div>
                <div class="buttons">
                    <button class="play">Играть!</button>
                    <button class="abort" disabled="true">Закончить</button>
                </div>
            </div>`);
        appendChildren(parent, elements);
        if (!withNameInput) {
            parent.querySelector('.inputs .name').remove();
        }
        return {
            play: parent.querySelector('.inputs .play'),
            abort: parent.querySelector('.inputs .abort'),
            serverUrl: parent.querySelector('.inputs .server-url'),
            name: withNameInput ? parent.querySelector('.inputs .name') : undefined
        };
    }
    
    constructor(controls, messages = InputManager.DEFAULT_MSGS) {
        this._play = controls.play;
        this._abort = controls.abort;
        this._serverUrlInput = controls.serverUrl;
        this._nameInput = controls.name;
        this._messages = messages;
    }
    
    onCredentials(handler) {
        this._play.addEventListener('click', () => {
            const serverUrl = this._serverUrlInput.value.trim();
            if (!serverUrl) {
                this.message(this._messages.enterUrl);
                return;
            }
            
            this._toggleControls();
            const name = this._nameInput && this._nameInput.value.trim() || null;
            handler(name, serverUrl);
        });
    }
    
    onAbort(handler) {
        const abortHdl = () => {
            const sure = confirm(this._messages.sureAbort);
            if (!sure) {
                return false;
            }
            handler();
        };
        listenOnce(this._abort, 'click', abortHdl);
    }
    
    onClose() {
        this._toggleControls();
    }
    
    _toggleControls() {
        [this._play, this._abort].map(toggleDisabled);
        [this._serverUrlInput, this._nameInput].filter(id).map(toggleDisplay);
    }
}

class PromptInputManager {
    static DEFAULT_MSGS = {
        enterName: 'Введите имя'
    };
    
    constructor(messages = PromptInputManager.DEFAULT_MSGS) {
        this._messages = messages;
    }
    
    onCredentials(handler) {
        const serverPath = location.pathname.slice(0, -1);
        const name = prompt(this._messages.enterName);
        handler(name, `ws://91.191.245.9:8080${serverPath}`);
    }
    
    onAbort(handler) {}
    
    onClose() {}
}

class MultiplayerGameController {
    constructor(mpElements, ctrlFactory) {
        this._ctrlFactory = ctrlFactory;
        this._addPlayer = mpElements.addPlayer;
    }
    
    onPlay(hdl) {
        this._initCtrl(hdl);
        this._addPlayer.addEventListener('click', () => this._initCtrl(hdl));
    }
    
    _initCtrl(hdl) {
        const [ctrl, wrapper] = this._ctrlFactory();
        ctrl.onPlay(conn => {
            conn.on(WebsocketConn.RecvMsg.START_GAME, () => hide(this._addPlayer));
            conn.on(WebsocketConn.RecvMsg.YOUR_TURN, () => displayBetweenSiblings(wrapper));
            hdl(conn, ctrl, wrapper);
        });
    }
}
