<?php

namespace B2P\Requests;

use B2P\Attributes\RequestParam;
use B2P\Models\Parameters\Id;
use B2P\Models\Parameters\Sector;
use B2P\Models\Parameters\Signature;

class Order extends AbstractRequest
{
    const PATH = 'webapi/info/Order';
    const METHOD = 'POST';

    /**
     * @var Id Уникальный идентификатор Заказа в ПЦ
     */
    #[RequestParam(required: true)]
    protected Id $id;

    protected function getSignature(): string
    {
        return Signature::make([$this->sector,$this->id, $this->configManager->getPass()]);
    }
}