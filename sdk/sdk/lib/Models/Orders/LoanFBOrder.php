<?php

namespace B2P\Models\Orders;

use B2P\Client;
use B2P\Models\Interfaces\SpecificOrder;
use B2P\Models\Interfaces\CreditOrder;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Кредитный заказ с вендором FinBox
 */
class LoanFBOrder extends Order implements SpecificOrder, CreditOrder
{
    public function __construct(\B2P\Responses\Order $orderDTO, Client $client)
    {
        $this->client = $client;
        return $this->getInstance($orderDTO);
    }

    /**
     * @param \B2P\Responses\Order $orderDTO
     * @return bool
     */
    public function canHandle(\B2P\Responses\Order $orderDTO): bool
    {
        return ($orderDTO->getParameterValue('fb_application_id') || isset($orderDTO->fb_application_id));
    }

    /**
     * @param \B2P\Responses\Order $orderDTO
     * @return Order|null
     */
    public function getInstance(\B2P\Responses\Order $orderDTO): ?Order
    {
        if ($this->canHandle($orderDTO)) {
            if (isset($this->next)) unset($this->next);
            return $this->buildInstance($orderDTO);
        }
        return (isset($this->next) && $this->next) ? $this->next->getInstance($orderDTO) : null;
    }

    public function isPaid(): bool
    {
        $loan_operation = null;
        foreach ($this->getOperations() as $operation)
            if ((string)$operation->type === 'LOAN') {
                $loan_operation = $operation;
                break;
            }
        return (parent::isPaid() && $loan_operation && (string)$loan_operation->state === 'APPROVED');
    }

    /**
     * @return bool
     * @throws GuzzleException
     */
    public function complete(): bool
    {
        $data = [
            'id' => $this->id,
            'amount' => $this->getParameterValue('pay_amount'),
            'currency' => $this->currency,
        ];
        return $this->client->complete($data);
    }

    public function reverse(): object
    {
        throw new \LogicException('Unable to issue a refund on a credit order');
    }
}