<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;

/**
 * Валюта
 */
class Language extends AbstractParameter
{
    protected string $value;

    /**
     * @param string|int $value буквенное обозначение языка (RU, EN)
     */
    public function __construct(string|int $value)
    {
        if ($value == 'EN') {
            $language = 'EN';
        } else {
            $language = 'RU';
        }

        if (isset($language)) {
            $this->value = $language;
        } else {
            throw new NotValidParamException('Incorrect Language parameter format. Only RU or EN');
        }
    }

    public function __toString(): string
    {
        return (string)$this->value;
    }
}