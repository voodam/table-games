<?php
namespace Games\Card;

use Games\Team;

interface PartieInterface {
    function deal(): void;
    function determineTrump(Trump $trump);
    function putCard(CardPlayer $player, Card $card): void;
    function ended(): bool;
    function gameScore(Team $team): int;
    function next(): self;
}
