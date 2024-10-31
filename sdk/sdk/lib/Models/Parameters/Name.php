<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;

/**
 * Имя на карте
 */
class Name extends AbstractParameter
{
    protected string $value;

    public function __construct(string $value)
    {
        if (strlen($value) <= 100) {
            $this->value = $value;
            return;
        }
        throw new NotValidParamException('Too long Name (>100)');
    }
}