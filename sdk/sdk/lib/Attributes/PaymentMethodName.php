<?php

namespace B2P\Attributes;

use Attribute;

/**
 * Этот атрибут содержит локализованные имена методов оплаты
 * при инициализации необходимо передать массив вида `['LANG_CODE' => 'NAME', ... ]`
 */
#[Attribute]
class PaymentMethodName
{
    /**
     * @param array $translations
     */
    public function __construct(public array $translations) {}
}