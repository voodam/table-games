<?php
namespace Games\Util\Translate;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;

function t(string $message, array $params = []): string {
    static $translator = null;
    if (!$translator) {
        $translator = new Translator('ru_RU');
        $translator->addLoader('yaml', new YamlFileLoader());
        $translator->addResource('yaml', 'messages.ru.yaml', 'ru_RU');
    }
    
    return $translator->trans($message, $params);
}
