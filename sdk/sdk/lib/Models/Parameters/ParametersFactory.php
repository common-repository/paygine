<?php

namespace B2P\Models\Parameters;

use B2P\Common\Exceptions\RequestParamException;

/**
 * Фабрика параметров
 *
 * Отвечает за создание DTO параметров
 */
class ParametersFactory
{
    /**
     * Создать объект параметра
     *
     * Подбор нужного класса параметра осуществляется по названию параметра.
     * Валидация значения происходит в конструкторе класса параметра.
     *
     * @param string $paramName
     * @param mixed $paramValue
     * @param string $type - тип ответа (order, operation, etc)
     * @return AbstractParameter
     */
    public static function make(string $paramName, mixed $paramValue, string $type = ''): AbstractParameter
    {
        try {
            return match (strtolower($paramName)) {
                'fee', 'amount' => new Amount($paramValue),
                'currency' => new Currency($paramValue),
                'date' => new Date($paramValue),
                'description' => new Description($paramValue),
                'email' => new Email($paramValue),
                'fio', 'name' => new Name($paramValue),
                'message' => new Message($paramValue),
                'id', 'order_id', 'operation' => new Id($paramValue),
                'operation_state' => new OperationState($paramValue),
                'inprogress' => new InProgress($paramValue),
                'pan' => new Pan($paramValue),
                'phone' => new Phone($paramValue),
                'reference' => new Reference($paramValue),
                'sector' => new Sector($paramValue),
                'signature' => new Signature($paramValue),
                'state' => ($type === 'operation')
                    ? (new OperationState($paramValue))
                    : (($type === 'order') ? (new OrderState($paramValue)) : (new Nonspecific($paramValue))),
                'order_state' => new OrderState($paramValue),
                'url', 'failurl' => new Url($paramValue),
                'notify_customer', 'mode' => new Mode($paramValue),
                'lang' => new Language($paramValue),
                'fiscal_positions' => new FiscalPositions($paramValue),
                'reason_code' => new ReasonCode($paramValue),
                'shop_cart' => new ShopCart($paramValue),
                'type' => new Type($paramValue),
                default => new Nonspecific($paramValue),
            };
        } catch (\Exception $e) {
            throw new RequestParamException($e->getMessage());
        }
    }
}