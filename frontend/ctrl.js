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

    static createDefaultElements(parent) {
        const elements = createElemsFromStr(
            `<div class="info">
                <div><div>Игровой счет</div><div class="score">0</div></div>
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

    constructor(inputManager, elements, messages = GameController.DEFAULT_MSGS) {
        this._inputManager = inputManager;
        this._logMessages = elements.messages;
        this._scoreStatus = elements.score;
        this._headerMessage = elements.header;
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
                this.headerMessage(this._messages.gameAborted);
                this._inputManager.onClose();
            });

            this.messagesOn(conn, {
                [WebsocketConn.RecvMsg.WAIT_PLAYERS]: this._messages.waitPlayers,
                [WebsocketConn.RecvMsg.WRONG_TURN]: this._messages.wrongTurn,
                [WebsocketConn.RecvMsg.WINNER_IS]: this._messages.winnerIs
            });
            this.messagesOn(conn, {
                [WebsocketConn.RecvMsg.TURN_OF]: this._messages.turnOf,
                [WebsocketConn.RecvMsg.YOUR_TURN]: this._messages.yourTurn
            }, this.headerMessage.bind(this));
            conn.on(WebsocketConn.RecvMsg.GAME_SCORE, (score) => {
                const elements = Object.keys(score).map(name => div(`${name}: ${score[name]}`));
                appendChildren(this._scoreStatus, elements, true);
            });
            
            hdl(conn);
        });
    }
    
    headerMessage(msg) {
        this._headerMessage.textContent = msg;
    }

    logMessage(msg) {
        this._logMessages.prepend(div(msg));
    }
    
    messageOn(conn, msgType, msg, messager = this.logMessage.bind(this)) {
        conn.on(msgType, payload => {
            messager(format(msg, ...toArray(payload)));
        });
    }

    messagesOn(conn, messages, messager = this.logMessage.bind(this)) {
        for (const [type, msg] of Object.entries(messages)) {
            this.messageOn(conn, type, msg, messager);
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
                this.logMessage(this._messages.enterUrl);
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
        if (name !== null) {
            handler(name, `ws://91.191.245.9:8080${serverPath}`);
        }
    }
    
    onAbort(handler) {}
    
    onClose() {}
}

class MultiplayerGameController {
    constructor(mpElements, ctrlFactory) {
        this._ctrlFactory = ctrlFactory;
        this._addPlayer = mpElements.addPlayer;
        this._playersNumber = 0;
        this._displayOnMessages = [];
    }
    
    onPlay(hdl) {
        this._initCtrl(hdl);
        this._addPlayer.addEventListener('click', () => this._initCtrl(hdl));
    }
    
    displayOn(...msgTypes) {
        this._displayOnMessages = this._displayOnMessages.concat(msgTypes);
    }
    
    get playersNumber() { return this._playersNumber; }
    
    _initCtrl(hdl) {
        const [ctrl, wrapper] = this._ctrlFactory();
        const displayWrapper = () => displayBetweenSiblings(wrapper);
        this._playersNumber++;
        if (this._playersNumber === 1) {
            displayWrapper();
        }

        ctrl.onPlay(conn => {
            console.assert(this._playersNumber > 0);
            
            conn.on(WebsocketConn.RecvMsg.START_GAME, () => {
                if (this._playersNumber === 1) {
                    displayWrapper();
                } else {
                    for (const msgType of this._displayOnMessages.concat(WebsocketConn.RecvMsg.YOUR_TURN)) {
                        conn.on(msgType, displayWrapper);
                    }
                }
                hide(this._addPlayer);
            });
            conn.onClose(() => {
                show(this._addPlayer);
                this._playersNumber--;
            });
            hdl(conn, ctrl, wrapper);
        });
    }
}
