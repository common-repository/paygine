<?php

namespace B2P\Responses;

use B2P\Attributes\ResponseParam;
use B2P\Models\Parameters\Amount;
use B2P\Models\Parameters\Currency;
use B2P\Models\Parameters\Date;
use B2P\Models\Parameters\Description;
use B2P\Models\Parameters\Email;
use B2P\Models\Parameters\Id;
use B2P\Models\Parameters\InProgress;
use B2P\Models\Parameters\OrderState;
use B2P\Models\Parameters\ParameterInterface;
use B2P\Models\Parameters\Phone;
use B2P\Models\Parameters\Reference;
use B2P\Models\Parameters\Url;

/**
 * Объект заказа в ПЦ
 */
class Order extends AbstractResponse
{
    /**
     * @var Id Номер Заказа в ПЦ
     */
    #[ResponseParam]
    protected Id $id;

    /**
     * @var OrderState Статус заказа в ПЦ
     */
    #[ResponseParam]
    protected OrderState $state;

    /**
     * @var Inprogress Признак нахождения Заказа в процессе оплаты
     */
    #[ResponseParam('inprogress')]
//    protected InProgress $inProgress;
    protected Inprogress $inprogress;

    /**
     * @var Date Дата и время регистрации Заказа в ПЦ
     */
    #[ResponseParam]
    protected Date $date;

    /**
     * @var Amount Сумма заказа в минимальных единицах валюты
     */
    #[ResponseParam]
    protected Amount $amount;

    /**
     * @var Currency Код валюты по ISO4217
     */
    #[ResponseParam]
    protected Currency $currency;

    /**
     * @var Email Адрес электронной почты Плательщика
     */
    #[ResponseParam]
    protected Email $email;

    /**
     * @var Phone Телефон Плательщика
     */
    #[ResponseParam]
    protected Phone $phone;

    /**
     * @var Reference Номер Заказа на стороне ТСП
     */
    #[ResponseParam]
    protected Reference $reference;

    /**
     * @var Description Описание заказа
     */
    #[ResponseParam]
    protected Description $description;

    /**
     * @var Url Адрес страницы на стороне ТСП, на которую по завершении Операции
     *          переводится Плательщик вместо показа ему типового чека ПЦ
     */
    #[ResponseParam]
    protected Url $url;

    /**
     * @var array $operations - массив всех операций по заказу
     */
    #[ResponseParam]
    protected array $operations;

    /**
     * @var ParameterInterface[] дополнительные параметры, переданные в запросе
     * @todo разобраться нужен ли тут атрибут параметра.
     *      выяснить участвует ли параметр в сигнатуре.
     */
    protected array $parameters;

    /**
     * @var string Цифровая подпись сообщения
     */
    #[ResponseParam(isInSignature: false)]
    protected string $signature;

    public function __set(string $name, $val)
    {
        if ($name === 'operations') {
            $operationArray = [];
            if ($val['@attributes']['number'] > 1)
                foreach ($val['operation'] as $operation)
                    $operationArray[$operation['id']] = ResponsesFactory::make('operation', $operation);
            else $operationArray[$val['operation']['id']] = ResponsesFactory::make('operation', $val['operation']);
            $val = $operationArray;
        }
        parent::__set($name, $val);
    }
}