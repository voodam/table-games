<?php
namespace Games\Card;

interface TrickInterface {
    function putCard(CardPlayer $player, Card $card): void;
    function calculateScore(): int;
    function winner(): CardPlayer;
    function ended(): bool;
}
