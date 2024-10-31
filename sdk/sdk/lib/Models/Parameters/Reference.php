<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;

/**
 * Номер Заказа на стороне ТСП
 */
class Reference extends AbstractParameter
{
    protected string $value;

    function __construct(string $value)
    {
        $value = (string) $value;
        if (strlen($value) <= 100) {
            $this->value = $value;
            return;
        }
        throw new NotValidParamException('Incorrect Reference parameter format');
    }
}