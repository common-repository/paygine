<?php

namespace B2P\Attributes;

use Attribute;

/**
 * Этим атрибутом помечаются свойства запросов, которые являются параметрами при отправке запроса
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class RequestParam
{
    /**
     * @param string $paramName название параметра запроса, с которым ассоциировано помеченное свойство.
     *                  Имеет смысл указывать, если название свойства отличается от названия параметра запроса
     * @param bool $required является ли параметр обязательным
     */
    public function __construct(
        public string $paramName = '',
        public bool $required = false
    ) {}
}