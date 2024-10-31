<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;
use DateTime;
use Exception;

/**
 * Дата и время
 *
 * @todo Разобраться и доработать верный часовой пояс
 */
class Date extends AbstractParameter
{
    protected DateTime $value;

    public function __construct(string $value, $timezone = null)
    {
        try {
            $this->value = DateTime::createFromFormat('Y.m.d H:i:s', $value, $timezone);
        } catch (Exception $e) {
            throw new NotValidParamException('Incorrect Date parameter format');
        }
    }

    public function __toString(): string
    {
        return $this->value->format('Y.m.d H:i:s');
    }
}