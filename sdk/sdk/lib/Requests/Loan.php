<?php

namespace B2P\Requests;

use B2P\Attributes\RequestParam;
use B2P\Models\Parameters\Id;
use B2P\Models\Parameters\Reference;
use B2P\Models\Parameters\Signature;

class Loan extends AbstractClientRequest
{
    const PATH = 'webapi/custom/unicheckout/PurchaseWithLoanManager';
    const METHOD = 'POST';

    /**
     * @var Id Уникальный идентификатор Заказа в ПЦ
     */
    #[RequestParam(required: true)]
    protected Id $id;

    /**
     * @var Reference Уникальный идентификатор Заказа в ИС ТСП
     */
    #[RequestParam(required: true)]
    protected Reference $reference;

    protected function getSignature(): string
    {
        $data = [$this->sector, $this->id, $this->reference];
        $data[] = $this->configManager->getPass();

        return Signature::make($data);
    }
}