<?php
namespace Games\Test\Unit;

use Games\Card\Goat\GoatTrump;
use Games\Card\Card;
use Games\Card\Rank;
use Games\Card\Suit;
use Games\Util\Cmp;

class GoatTrumpTest {
    public function run() {
        $this->haveSameSuit();
        $this->compare();
    }
    
    private function haveSameSuit() {
        $trump = new GoatTrump(Suit::DIAMONDS());
        assert($trump->haveSameSuits( new Card(Rank::_7(), Suit::HEARTS()), new Card(Rank::_10(), Suit::HEARTS()) ));
        assert($trump->haveSameSuits( new Card(Rank::_7(), Suit::DIAMONDS()), new Card(Rank::_10(), Suit::DIAMONDS()) ));
        assert(!$trump->haveSameSuits( new Card(Rank::JACK(), Suit::CLUBS()), new Card(Rank::_9(), Suit::CLUBS()) ));
        assert($trump->haveSameSuits( new Card(Rank::JACK(), Suit::DIAMONDS()), new Card(Rank::_9(), Suit::DIAMONDS()) ));
        assert($trump->haveSameSuits( new Card(Rank::JACK(), Suit::CLUBS()), new Card(Rank::JACK(), Suit::HEARTS()) ));
        assert($trump->haveSameSuits( new Card(Rank::JACK(), Suit::CLUBS()), new Card(Rank::QUEEN(), Suit::DIAMONDS()) ));
    }
    
    private function compare() {
        $trump = new GoatTrump(Suit::DIAMONDS());
        assert($trump->compare( new Card(Rank::_8(), Suit::HEARTS()), new Card(Rank::_8(), Suit::HEARTS()) ) === Cmp::EQ);
        
        assert($trump->compare( new Card(Rank::_7(), Suit::HEARTS()), new Card(Rank::_10(), Suit::HEARTS()) ) === Cmp::LESS);
        assert($trump->compare( new Card(Rank::_7(), Suit::HEARTS()), new Card(Rank::JACK(), Suit::HEARTS()) ) === Cmp::LESS);
        assert($trump->compare( new Card(Rank::JACK(), Suit::HEARTS()), new Card(Rank::QUEEN(), Suit::HEARTS()) ) === Cmp::MORE);
        assert($trump->compare( new Card(Rank::KING(), Suit::HEARTS()), new Card(Rank::_10(), Suit::HEARTS()) ) === Cmp::LESS);
        assert($trump->compare( new Card(Rank::_10(), Suit::HEARTS()), new Card(Rank::ACE(), Suit::HEARTS()) ) === Cmp::LESS);
        assert($trump->compare( new Card(Rank::_8(), Suit::HEARTS()), new Card(Rank::ACE(), Suit::SPADES()) ) === Cmp::MORE);
        
        assert($trump->compare( new Card(Rank::_7(), Suit::DIAMONDS()), new Card(Rank::_10(), Suit::DIAMONDS()) ) === Cmp::MORE);
        assert($trump->compare( new Card(Rank::JACK(), Suit::CLUBS()), new Card(Rank::_7(), Suit::DIAMONDS()) ) === Cmp::LESS);
        assert($trump->compare( new Card(Rank::JACK(), Suit::SPADES()), new Card(Rank::JACK(), Suit::CLUBS()) ) === Cmp::LESS);
        assert($trump->compare( new Card(Rank::JACK(), Suit::HEARTS()), new Card(Rank::JACK(), Suit::SPADES()) ) === Cmp::LESS);
        assert($trump->compare( new Card(Rank::JACK(), Suit::DIAMONDS()), new Card(Rank::JACK(), Suit::HEARTS()) ) === Cmp::LESS);
        assert($trump->compare( new Card(Rank::ACE(), Suit::DIAMONDS()), new Card(Rank::JACK(), Suit::DIAMONDS()) ) === Cmp::LESS);
    }
}
