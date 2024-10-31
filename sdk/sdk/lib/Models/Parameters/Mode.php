<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;

/**
 * Установка режима со значениями 0 или 1. Например, параметры mode, notify_customer
 */
class Mode extends AbstractParameter
{
    protected int $value;

    function __construct($value)
    {
        $value = intval($value);
        if ($value == 0 | 1) {
            $this->value = $value;
            return;
        }
        throw new NotValidParamException('Incorrect Mode parameter format. Only 0 or 1.');
    }
}