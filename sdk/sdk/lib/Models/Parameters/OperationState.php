<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;
use B2P\Models\Enums\OperationStatus;

/**
 * Статус операции
 */
class OperationState extends AbstractParameter
{
    protected OperationStatus $value;

    public function __construct(string $value)
    {
        $state = null;
        foreach (OperationStatus::cases() as $status) {
            if ($value === $status->name) {
                $state = $status;
                break;
            }
        }
        if (isset($state)) {
            $this->value = $state;
        } else {
            throw new NotValidParamException('Incorrect State of Operation parameter format');
        }
    }

    public function __toString(): string
    {
        return $this->value->name;
    }
}