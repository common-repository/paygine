<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;

class Sector extends AbstractParameter
{
    protected int $value;

    function __construct(int $value)
    {
        $value = intval($value);
        if ($value > 0 && strlen((string)$value) <= 9) {
            $this->value = $value;
            return;
        }
        throw new NotValidParamException('Incorrect Sector parameter format');
    }
}