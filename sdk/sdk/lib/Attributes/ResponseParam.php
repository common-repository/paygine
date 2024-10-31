<?php

namespace B2P\Attributes;

use Attribute;

/**
 * Этим атрибутом помечаются свойства, ассоциированные с оригинальными параметрами ответов
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class ResponseParam
{
    /**
     * @param string $paramName название параметра ответа, с которым ассоциировано помеченное свойство.
     *                  Имеет смысл указывать, если название свойства отличается от названия параметра ответа
     * @param bool $isInSignature участвует ли параметр в составлении сигнатуры
     */
    public function __construct(
        public string $paramName = '',
        public bool $isInSignature = true
    ) {}
}