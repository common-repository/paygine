<?php

namespace B2P\Models\Enums;

use ReflectionClass;

/**
 * Валюты
 *
 * Содержит все поддерживаемые коды валют по ISO4217
 */
class CurrencyCode
{
    const RUB = 643;
    const EUR = 978;
    const USD = 840;

    /**
     * @return array
     */
    public static function cases(): array
    {
        $class = new ReflectionClass(__CLASS__);
        return $class->getConstants();
    }

}