<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;

class Nonspecific extends AbstractParameter
{
    protected string $value;

    public function __construct(string $value)
    {
        if (strlen($value) <= 2000) {
            $this->value = $value;
            return;
        }
        throw new NotValidParamException('Too long non-specific parameter (>2000 symbols)');
    }
}