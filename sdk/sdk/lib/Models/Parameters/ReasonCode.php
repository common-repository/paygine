<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;
use B2P\Models\Enums\Reason;

/**
 * Код ответа на операцию ПЦ
 *
 * @method string desc()
 *                  {@see Reason::desc()}
 * @method string msg()
 *                  {@see Reason::msg()}
 */
class ReasonCode extends AbstractParameter
{
    protected Reason $value;

    public function __construct(string|int $value)
    {
        if (is_numeric($value)) {
            $currency = Reason::tryFrom(intval($value));
        } else {
            $currency = null;
            foreach (Reason::cases() as $currency_code) {
                if ($value === $currency_code->name) {
                    $currency = $currency_code;
                    break;
                }
            }
        }
        if (isset($currency)) {
            $this->value = $currency;
        } else {
            $this->value = Reason::tryFrom(-1);
            // throw new NotValidParamException('Incorrect Reason Code parameter format');
        }
    }

    public function __toString(): string
    {
        return (string)$this->value->value;
    }
}