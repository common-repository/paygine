<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;
use B2P\Models\Enums\OperationType;

/**
 * Тип операции
 */
class Type extends AbstractParameter
{
    protected OperationType $value;

    public function __construct(string $value)
    {
        $type = null;
        foreach (OperationType::cases() as $operationType) {
            if ($value === $operationType->name) {
                $type = $operationType;
                break;
            }
        }
        if (isset($type)) {
            $this->value = $type;
        } else {
            throw new NotValidParamException(sprintf('Incorrect Type of Operations parameter format: %s', print_r($value, true)));
        }
    }

    public function __toString(): string
    {
        return $this->value->name;
    }
}