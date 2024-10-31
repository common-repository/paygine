<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;

/**
 * Номер Заказа или Операции в ПЦ
 */
class Id extends AbstractParameter
{
    protected int $value;

    function __construct($value)
    {
        $value = intval($value);
        if ($value >= 0 && strlen((string)$value) <= 15) {
            $this->value = $value;
            return;
        }
        throw new NotValidParamException('Incorrect id parameter format');
    }
}
