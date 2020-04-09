class MultiplayerGameController {
    constructor(mpElements, elements, messages = GameController.DEFAULT_MSGS) {
        this._elements = elements;
        this._messages = messages;
        this._addUser = mpElements.addUser;
    }
    
    start() {
        this._addUser.addEventListener('click', () => {});
    }
}