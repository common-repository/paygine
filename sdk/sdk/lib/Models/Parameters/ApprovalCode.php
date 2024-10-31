<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;

/**
 * Код авторизации, полученный в Банке на данную операцию
 */
class ApprovalCode extends AbstractParameter
{
    protected int $value;

    function __construct(string|int $value)
    {
        $value = intval($value);
        if ($value >= 0) {
            $this->value = $value;
            return;
        }
        throw new NotValidParamException('Incorrect Approval Code parameter format');
    }
}