<?php
namespace Games\Card\Goat;

use Games\Card\Trick;
use Games\Card\Card;
use Games\Card\CardPlayer;
use Games\Exception\WrongTurnException;
use Games\Card\Rank;
use function Games\Util\Translate\t;

class GoatTrick extends Trick {
    protected function compareCards(Card $card1, Card $card2): int {
        return $card1->compareTrump($card2, $this->trump, [Rank::class, 'cmpOrder10']);
    }
    
    protected function constrainCard(CardPlayer $player, Card $putCard): void {
        if (count($this->cards) === 0) {
            return;
        }
        
        $firstCard = $this->cards->getFirstObject();
        assert($firstCard instanceof Card);
        
        if (!$firstCard->haveSameSuit($putCard, $this->trump) && $player->hasSuit($firstCard, $this->trump)) {
            $this->log('wrong turn: ' . implode(', ', iterator_to_array($this->cards)));
            throw new WrongTurnException(t("Player {0} must put a card with first card suit: {1}, given card: {2}", [$player, t($firstCard->suit()), $putCard->translate()]));
        }
    }
}
