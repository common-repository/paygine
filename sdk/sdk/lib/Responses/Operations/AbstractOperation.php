<?php

namespace B2P\Responses\Operations;

use B2P\Attributes\ResponseParam;
use B2P\Common\ConfigManager;
use B2P\Models\Parameters\Amount;
use B2P\Models\Parameters\ApprovalCode;
use B2P\Models\Parameters\Currency;
use B2P\Models\Parameters\Date;
use B2P\Models\Parameters\Email;
use B2P\Models\Parameters\Id;
use B2P\Models\Parameters\Message;
use B2P\Models\Parameters\Name;
use B2P\Models\Parameters\OperationState;
use B2P\Models\Parameters\OrderState;
use B2P\Models\Parameters\Pan;
use B2P\Models\Parameters\ReasonCode;
use B2P\Models\Parameters\Reference;
use B2P\Models\Parameters\Signature;
use B2P\Models\Parameters\Type;
use B2P\Responses\AbstractResponse;
use ReflectionObject;

/**
 * Операция проводимая в рамках заказа
 */
abstract class AbstractOperation extends AbstractResponse
{
    /**
     * @var Id идентификатор Заказа, в рамках которого была операция
     */
    #[ResponseParam('order_id')]
    protected Id $orderID;

    /**
     * @var OrderState статус Заказа после выполнения Операции
     */
    #[ResponseParam('order_state')]
    protected OrderState $orderState;

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
     * @var Type тип операции, отправленной в ПЦ
     */
    #[ResponseParam]
    protected Type $type;

    /**
     * @var OperationState результат выполнения операции в ПЦ
     */
    #[ResponseParam]
    protected OperationState $state;

    /**
     * @var ReasonCode код ответа на операцию ПЦ
     */
    #[ResponseParam('reason_code')]
    protected ReasonCode $reasonCode;

    /**
     * @var Message краткое описание кода ответа на операцию ПЦ
     */
    #[ResponseParam]
    protected Message $message;

    /**
     * @var Name имя на банковской карте Плательщика
     */
    #[ResponseParam]
    protected Name $name;

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
     * @var Amount сумма операции
     */
    #[ResponseParam]
    protected Amount $amount;

    /**
     * @var Currency валюта операции
     */
    #[ResponseParam]
    protected Currency $currency;

    /**
     * @var ApprovalCode код авторизации, полученный в Банке на данную операцию
     */
    #[ResponseParam('approval_code')]
    protected ApprovalCode $approvalCode;

    /**
     * @var Signature цифровая подпись сообщения
     */
    #[ResponseParam(isInSignature: false)]
    protected Signature $signature;

    /**
     * Проверить корректность сигнатуры
     *
     * @return bool
     */
    public function checkSignature(): bool
    {
        if (!isset($this->signature))
            return false;

        $data = [];
        $reflection_obj = new ReflectionObject($this);
        foreach ($reflection_obj->getProperties() as $reflection_property) {
            $attributes = $reflection_property->getAttributes(ResponseParam::class);
            if (count($attributes) > 0) {
                /**
                 * @var ResponseParam $attr
                 */
                $attr = $attributes[0]->newInstance();
                if ($attr->isInSignature && $reflection_property->isInitialized($this)) {
                    $data[] = (string)$reflection_property->getValue($this);
                }
            }
        }

        $data[] = ConfigManager::getInstance()->getPass();

        return $this->signature->isEqualsTo($data);
    }
}