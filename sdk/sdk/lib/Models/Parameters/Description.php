<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;

/**
 * Описание Заказа
 */
class Description extends AbstractParameter
{
    protected string $value;

    public function __construct(string $value)
    {
        if (strlen($value) <= 1000) {
            $this->value = $value;
            return;
        }
        throw new NotValidParamException('Too long Description (>1000)');
    }
}