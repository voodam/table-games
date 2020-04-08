<?php
namespace Games;

use MyCLabs\Enum\Enum;
use function Games\Util\Iter\flat;

trait MsgObservable {
    private array $observers = [];

    public function getObserversRec(string $message): array {
        $observers = $this->getObservers($message);
        $isObservable = fn(object $observer) => $observer !== $this && $observer instanceof MsgObservableInterface;
        $observables = array_filter(flat($this->observers), $isObservable);
        $reducer = fn(array $observers, MsgObservableInterface $observable) => array_merge($observers, $observable->getObserversRec($message));
        return array_reduce($observables, $reducer, $observers);
    }

    protected function attachObserver(object $observer, Enum $message): void {
        $this->observers[$message->getValue()] ??= [];
        $this->observers[$message->getValue()][] = $observer;
    }

    protected function detachObserver(?object $observer, Enum $message): void {
        $key = array_search($observer, $this->getObservers($message));
        if ($key !== false) {
            unset($this->observers[$message][$key]);
        }
    }

    private function getObservers(string $message): array { return $this->observers[$message] ?? []; }
}
