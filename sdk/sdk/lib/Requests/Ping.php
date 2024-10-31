<?php

namespace B2P\Requests;

use B2P\Models\Parameters\Signature;

/**
 * Параметры передавать не требуется.
 * В ответ на запрос webapi/Ping приходит строка Pong.
 */
class Ping extends AbstractRequest
{
    const PATH = 'webapi/Ping';
    const METHOD = 'POST';

    protected function getSignature(): string
    {
        return Signature::make([$this->sector, $this->configManager->getPass()]);
    }
}