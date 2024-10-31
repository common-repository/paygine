<?php

namespace B2P\Requests;

use B2P\Attributes\RequestParam;
use B2P\Models\Parameters\Amount;
use B2P\Models\Parameters\Currency;
use B2P\Models\Parameters\Description;
use B2P\Models\Parameters\Id;
use B2P\Models\Parameters\Signature;

class Complete extends AbstractRequest
{
    const PATH = 'webapi/Complete';
    const METHOD = 'POST';

    /**
     * @var Id Уникальный идентификатор Заказа в ПЦ
     */
    #[RequestParam(required: true)]
    protected Id $id;

    /**
     * @var Amount Сумма заказа в минимальных единицах валюты. Для рублей — копейки
     */
    #[RequestParam(required: true)]
    protected Amount $amount;

    /**
     * @var Currency Код валюты по ISO4217
     */
    #[RequestParam(required: true)]
    protected Currency $currency;


    protected function getSignature(): string
    {
        return Signature::make([$this->sector,$this->id, $this->amount, $this->currency, $this->configManager->getPass()]);
    }
}