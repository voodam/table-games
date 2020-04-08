<?php
namespace Games\Util;

use function Games\Util\Iter\filter;

class MyObjectStorage extends \SplObjectStorage {
    public function updateInfo(object $object, callable $handler) {
        $info = $this[$object] ?? null;
        $info = $handler($info);
        $this[$object] = $info;
    }

    public function setInfoByKey(object $object, $key, $value) {
        $this->updateInfo($object, function($info) use ($key, $value) {
            $info ??= [];
            $info[$key] = $value;
            return $info;
        });
    }

    public function getOtherInfo(object $object): array {
        $otherObjects = filter($this, fn($obj) => $obj !== $object);
        return array_map(fn($obj) => $this[$obj], $otherObjects);
    }

    public function getFirstInfo() {
        foreach ($this as $object)
            return $this[$object];
    }

    public function getFirstObject(): object {
        foreach ($this as $object)
            return $object;
    }

    public function detachAll() {
        $this->rewind();
        while ($this->valid()) {
            $item = $this->current();
            $this->next();
            $this->detach($item);
        }
        assert(count($this) === 0);
    }
}
