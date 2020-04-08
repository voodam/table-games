<?php
namespace Games\Card\Goat;

use Games\Card\Trick;
use Games\Card\Card;
use Games\Card\CardPlayer;
use Games\Exception\WrongTurnException;
use function Games\Util\Translate\t;

class GoatTrick extends Trick {
    protected function constrainCard(CardPlayer $player, Card $card): void {
        if (count($this->cards) === 0) {
            return;
        }
        
        $firstCard = $this->cards->getFirstObject();
        assert($firstCard instanceof Card);
        if (!$firstCard->haveSameSuit($card) && $player->hasSuit($firstCard)) {
            $this->log('wrong turn: ' . implode(', ', iterator_to_array($this->cards)));
            throw new WrongTurnException(t("Player {0} must put a card with first card suit: {1}, given card: {2}", [$player, t($firstCard->suit()), $card->translate()]));
        }
    }
}
