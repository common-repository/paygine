<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;

/**
 * Признак нахождения Заказа в процессе оплаты:
 *      0 — в рамках Заказа нет активных Операций
 *      1 — в рамках Заказа есть активная Операция
 */
class InProgress extends AbstractParameter
{
    protected int $value;

    function __construct(string|int $value)
    {
        $value = intval($value);
        if ($value === 0 || $value === 1) {
            $this->value = $value;
            return;
        }
        throw new NotValidParamException('Incorrect InProgress parameter format');
    }
}