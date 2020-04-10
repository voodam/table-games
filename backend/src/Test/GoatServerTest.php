<?php
namespace Games\Test;

use Games\Card\Goat\GoatServer;
use Games\Card\CardRecvMsg;
use Games\Card\Suit;
use Games\Player;
use Games\SendMsg;
use Games\Card\CardSendMsg;
use Games\Card\CardPlayer;

class GoatServerTest extends ServerTest {
    protected function msgHandler(Player $player, string $type, $payload = null): void {
        switch($type) {
            case CardSendMsg::ASK_TRUMP()->getValue(): 
                $this->onMessage($player, CardRecvMsg::DETERMINE_TRUMP(), Suit::random());
                break;
            case SendMsg::YOUR_TURN()->getValue():
                $this->putRandomCard($player);
                break;
            case SendMsg::WRONG_TURN()->getValue():
                $this->putRandomCard($player);
                break;
        }
    }
    
    private function putRandomCard(CardPlayer $player): void {
        $this->onMessage($player, CardRecvMsg::PUT_CARD(), $player->randomCard());
    }
    
    protected function gamesNumber(): int { return 2; }
    protected function createServer(): GoatServer { return new GoatServer; }
}
