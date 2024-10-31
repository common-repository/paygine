<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;

/**
 * Краткое текстовое сообщение
 */
class Message extends AbstractParameter
{
    protected string $value;

    public function __construct(string $value)
    {
        if (strlen($value) <= 1000) {
            $this->value = $value;
            return;
        }
        throw new NotValidParamException('Too long Message (>1000)');
    }
}