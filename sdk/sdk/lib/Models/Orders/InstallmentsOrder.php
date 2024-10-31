<?php

namespace B2P\Models\Orders;

use B2P\Client;
use B2P\Models\Interfaces\SpecificOrder as SpecificOrder;
use B2P\Models\Parameters\Amount;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Заказ с рассрочкой оплаты - Плайт (Plait), Подели (Podeli).
 */
class InstallmentsOrder extends Order implements SpecificOrder
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
        return (bool)$orderDTO->getParameterValue('buyIdSumAmount') || (bool)$orderDTO->getParameterValue('podeli_amount');
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

    /**
     * @return Amount
     */
    public function getActualAmount(): Amount
    {
        return new Amount($this->getParameterValue('buyIdSumAmount'));
    }

    /**
     * @return bool
     * @throws GuzzleException
     */
    public function complete(): bool
    {
        $data = [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];

        return $this->client->complete($data);
    }

    /**
     * Метод возвращает захолдированную сумму по заказу
     *
     * @return int
     */
    public function getHoldAmount(): int
    {
        if ($this->getState() !== 'AUTHORIZED') return 0;
        $amount = 0;
        foreach ($this->getOperations() as $operation)
            if ((string)$operation->type === 'AUTHORIZE' && (string)$operation->state === 'APPROVED')
                $amount += $operation->amount->getValue();

        return $amount;
    }

}