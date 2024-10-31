<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;

class ShopCart extends AbstractParameter
{
    protected string $value;

    public function __construct(string $value)
    {
        if (strlen($value) <= 2000 /*&& is_array_assoc(json_decode(base64_decode($value)))*/) {
            $this->value = $value;
            return;
        }
        throw new NotValidParamException('Too long ShopCart (>2000)');
    }
}