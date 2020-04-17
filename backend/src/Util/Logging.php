<?php
namespace Games\Util;

trait Logging {
    protected function log(string $msg): void {
        if (!defined('DISABLE_LOGGING') || DISABLE_LOGGING === false) {
            echo $msg . "\n";
        }
    }
    
    protected function error(string $msg): void {
        echo "[ERROR] $msg\n";
    }
    
    protected function safeDump($value): string {
        if ($value === '') return 'empty string';
        if (is_scalar($value)) return (string) $value;
        if (is_array($value)) return json_encode($value);
        if (is_object($value)) {
            if (method_exists($value, '__toString')) return (string) $value;
            if ($value instanceof \JsonSerializable) return json_encode($value);
            return 'object of class: ' . get_class($value);
        }
        return 'value of type: ' . gettype($value);
    }
}
