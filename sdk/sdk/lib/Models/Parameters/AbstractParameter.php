<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\NotValidParamException;
use BadMethodCallException;

/**
 * Олицетворение параметров участвующих в запросах между ТСП и ПЦ
 *
 * Является Объектом Значения. Не предполагает изменение значения после инициализации.
 * Конструктор должен валидировать и устанавливать значение параметра.
 * В случае ошибки валидации выбрасывает исключение.
 *
 * @throws NotValidParamException
 */
abstract class AbstractParameter implements ParameterInterface
{
    public function __toString(): string
    {
        return (string)$this->value;
    }

    public function getValue()
    {
        return $this->value;
    }

    /**
     * На случай если значение является объектом и необходимо вызвать его метод как собственный
     *
     * @param string $name
     * @param array $arguments
     * @return false|mixed
     */
    public function __call(string $name, array $arguments)
    {
        if (is_object($this->value) && method_exists($this->value, $name)) {
            return call_user_func_array([$this->value, $name], $arguments);
        }
        throw new BadMethodCallException();
    }
}