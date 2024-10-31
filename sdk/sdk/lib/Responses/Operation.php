<?php

namespace B2P\Responses;

use B2P\Attributes\ResponseParam;
//use B2P\Common\ConfigManager;
use B2P\Common\Exceptions\Exception;
use B2P\Models\Parameters\Id;
use B2P\Models\Parameters\OrderState;
use B2P\Models\Parameters\ReasonCode;
use B2P\Models\Parameters\Reference;
use B2P\Models\Parameters\Date;
use B2P\Models\Parameters\Type;
use B2P\Models\Parameters\OperationState;
use B2P\Models\Parameters\Message;
use B2P\Models\Parameters\Name;
use B2P\Models\Parameters\Pan;
use B2P\Models\Parameters\Email;
use B2P\Models\Parameters\Amount;
use B2P\Models\Parameters\Currency;
use B2P\Models\Parameters\ApprovalCode;
use B2P\Models\Parameters\Signature;

/**
 * Объект операции в ПЦ
 */
class Operation extends AbstractResponse
{

    /**
     * Операция проводимая в рамках заказа
     */

    /**
     * @var Id идентификатор Заказа, в рамках которого была операция
     */
    #[ResponseParam('order_id')]
    protected Id $order_id;

    /**
     * @var Reference номер Заказа на стороне ТСП в его Системе магазина
     */
    #[ResponseParam]
    protected Reference $reference;

    /**
     * @var Id идентификатор операции в ПЦ
     */
    #[ResponseParam]
    protected Id $id;

    /**
     * @var Date дата и время совершения операции в ПЦ
     */
    #[ResponseParam]
    protected Date $date;

    /**
     * @var Amount Сумма комиссии в минимальных единицах
     *
     * */
    #[ResponseParam]
    protected Amount $fee;

    /**
     * @var Type тип операции, отправленной в ПЦ
     */
    #[ResponseParam]
    protected Type $type;

    /**
     * @var Message краткое описание кода ответа на операцию ПЦ
     */
    #[ResponseParam]
    protected Message $message;

    /**
     * @var ReasonCode код ответа на операцию ПЦ
     */
    #[ResponseParam]
    protected ReasonCode $reason_code;

    /**
     * @var Name имя на банковской карте Плательщика
     */
    #[ResponseParam]
    protected Name $name;

    /**
     * @var Currency валюта операции
     */
    #[ResponseParam]
    protected Currency $currency;

    /**
     * @var OperationState результат выполнения операции в ПЦ
     */
    #[ResponseParam]
    protected OperationState $state;

    /**
     * @var Pan маскированный номер банковской карты Плательщика
     */
    #[ResponseParam]
    protected Pan $pan;

    /**
     * @var Email электронная почта Плательщика
     */
    #[ResponseParam]
    protected Email $email;

    /**
     * @var OrderState результат выполнения операции в ПЦ
     */
    #[ResponseParam('order_state')]
    protected OrderState $orderState;

    /**
     * @var Amount сумма операции
     */
    #[ResponseParam]
    protected Amount $amount;

    /**
     * @var ApprovalCode код авторизации, полученный в Банке на данную операцию
     */
    #[ResponseParam('approval_code')]
    protected ApprovalCode $approvalCode;

    /**
     * @var ParameterInterface[] дополнительные параметры, переданные в запросе
     * @todo разобраться нужен ли тут атрибут параметра.
     *      выяснить участвует ли параметр в сигнатуре.
     */
    protected array $parameters;

    /**
     * @var Signature цифровая подпись сообщения
     */
    #[ResponseParam(isInSignature: false)]
    protected Signature $signature;

}