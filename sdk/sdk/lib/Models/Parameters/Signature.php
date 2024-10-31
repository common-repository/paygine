<?php

namespace B2P\Models\Parameters;

use B2P\Common\ConfigManager;
use B2P\Common\Exceptions\NotValidParamException;

/**
 * Цифровая подпись
 */
class Signature extends AbstractParameter
{
    protected string $value;

    /**
     * @param string|array $value строка сигнатуры или массив данных для которого сигнатуру нужно сгенерировать
     */
    public function __construct($value)
    {
        if (is_string($value) && strlen($value) <= 255) {
            $this->value = $value;
            return;
        } elseif (is_array($value)) {
            $this->value = static::make($value);
            return;
        }
        throw new NotValidParamException('Too long Signature (>255)');
    }

    /**
     * Сгенерировать сигнатуру для массива данных
     *
     * @param array $data - массив значений, участвующих в формировании сигнатуры
     * @param bool $addSignPass - если true, к данным добавится пароль для формирования сигнатуры из ConfigManager
     * @return string
     */
    public static function make(array $data, bool $addSignPass = false): string
    {
        $algo = (ConfigManager::getInstance()->isSHAseted()) ? 'sha256' : 'md5';
        if ($addSignPass) $data[] = ConfigManager::getInstance()->getPass();
        return base64_encode(hash($algo, self::implodeIterable($data)));
    }

    public static function implodeIterable(iterable $content): string
    {
        $result = '';
        foreach ($content as $key => $value) {
            if (!is_iterable($value) && is_object($value)) $value = (array)$value;
            if ($key == 'number') continue;
            $result .= (is_iterable($value)) ? self::implodeIterable($value) : $value;
        }
        return $result;
    }

    /**
     * Проверить является ли текущая сигнатура эквивалентной переданной сигнатуре/данным
     *
     * @param string|array $value
     * @return bool
     */
    public function isEqualsTo($value): bool
    {
        $signature = is_string($value) ? $value : static::make($value);
        return $signature === $this->value;
    }
}