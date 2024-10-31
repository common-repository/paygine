<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;

/**
 * Сумма заказа в минимальных единицах валюты. Для рублей — копейки.
 */
class Amount extends AbstractParameter
{
    protected int $value;

    public function __construct(int $value)
    {
        $value = intval($value);
        if ($value >= 0 && strlen((string)$value) <= 12) {
            $this->value = $value;
            return;
        }
        throw new NotValidParamException('Incorrect Amount parameter format');
    }
}