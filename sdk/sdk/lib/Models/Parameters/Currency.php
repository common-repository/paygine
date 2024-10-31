<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;
use B2P\Models\Enums\CurrencyCode;

/**
 * Валюта
 */
class Currency extends AbstractParameter
{
    protected int $value;
    public function __construct($value)
    {
        $currency = null;
        foreach (CurrencyCode::cases() as $name => $code) {
            if ((int)$value === $code) {
                $currency = $code;
                break;
            }
        }

        if (isset($currency)) {
            $this->value = $currency;
        } else {
            throw new NotValidParamException('Incorrect Currency parameter format. Supported currencies: ' . implode(', ', CurrencyCode::cases()));
        }
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }
}