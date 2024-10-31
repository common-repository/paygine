<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;

/**
 * Время жизни заказа в ПЦ
 */
class LifePeriod extends AbstractParameter
{
    protected int $value;

    function __construct($value)
    {
        $value = intval($value);
        if ($value >= 0 && strlen((string)$value) <= 20) {
            $this->value = $value;
            return;
        }
        throw new NotValidParamException('Incorrect LifePeriod parameter format');
    }
}