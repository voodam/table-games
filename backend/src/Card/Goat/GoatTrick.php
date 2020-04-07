<?php
namespace Games\Card\Goat;

use Games\Card\Trick;
use Games\Card\CardPlayer;
use Games\Card\Card;
use Games\Card\Rank;
use function Games\Util\Translate\t;

class GoatTrick extends Trick {
    protected function compareCards(Card $card1, Card $card2): int {
        return $card1->compareTrump($card2, $this->trump, [Rank::class, 'cmpOrder10']);
    }
    
    protected function constrainCard(CardPlayer $player, Card $card): void {
        if (count($this->cards) === 0) {
            return;
        }
        
        $firstCard = $this->cards->getFirstInfo();
        if (!$firstCard->suit()->equals($card->suit()) && $player->hasSuit($firstCard->suit())) {
            throw new CardConstraintException(t("Player {0} must put a card with first card suit: {1}, given card: {2}", [$player, t($firstCard->suit()), $card->translate()]));
        }
    }
}
