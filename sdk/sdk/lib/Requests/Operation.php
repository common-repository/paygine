<?php

namespace B2P\Requests;

use B2P\Attributes\RequestParam;
use B2P\Models\Parameters\Signature;
use B2P\Models\Parameters\Id;
use B2P\Models\Parameters\Sector;

class Operation extends AbstractRequest
{
    const PATH = 'webapi/Operation';
    const METHOD = 'POST';

    /**
     * @var Id Уникальный идентификатор Заказа в ПЦ
     */
    #[RequestParam(required: true)]
    protected Id $id;

    /**
     * @var Id Уникальный идентификатор Операции в ПЦ
     */
    #[RequestParam(required: true)]
    protected Id $operation;

    protected function getSignature(): string
    {
        return Signature::make([$this->sector, $this->id, $this->operation, $this->configManager->getPass()]);
    }
}