<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;
use B2P\Models\Enums\OrderStatus;

/**
 * Статус Заказа после выполнения Операции
 */
class OrderState extends AbstractParameter
{
    protected OrderStatus $value;

    public function __construct(string $value)
    {
        $state = null;
        foreach (OrderStatus::cases() as $status) {
            if ($value === $status->name) {
                $state = $status;
                break;
            }
        }
        if (isset($state)) {
            $this->value = $state;
        } else {
            throw new NotValidParamException('Incorrect State of Order parameter format');
        }
    }

    public function __toString(): string
    {
        return $this->value->name;
    }
}