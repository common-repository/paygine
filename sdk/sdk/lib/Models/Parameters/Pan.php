<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;

/**
 * Номер карты или его маска
 */
class Pan extends AbstractParameter
{
    protected string $value;

    public function __construct(string $value)
    {
        if (strlen($value) <= 20) {
            $this->value = $value;
            return;
        }
        throw new NotValidParamException('Too long Pan (>20)');
    }
}