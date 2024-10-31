<?php

namespace B2P\Models\Orders;

use B2P\Client;
use B2P\Common\Exceptions\Exception;
use B2P\Models\Enums\OrderStatus;
use B2P\Models\Parameters\AbstractParameter;
use B2P\Models\Parameters\Amount;
use GuzzleHttp\Exception\GuzzleException;

/**
 * Базовый класс заказа
 */
class Order
{
    protected Client $client;
    protected OrderStatus $state;

    /**
     * @param \B2P\Responses\Order $orderDTO
     * @param Client $client
     */
    public function __construct(\B2P\Responses\Order $orderDTO, Client $client)
    {
        $this->client = $client;
        for ($i = 2; $i < count($files = scandir(__DIR__)); $i++) {
            class_exists('B2P\Models\Orders\\' . str_ireplace('.php', '', $files[$i]));
        }
        interface_exists('B2P\Models\Interfaces\SpecificOrder');

        if ($specificInstance = $this->getInstance($orderDTO)) {
            $this->specificInstance = $specificInstance;
        } else $this->buildInstance($orderDTO);
    }

    public function __get(string $name)
    {
        return (is_a($this->$name, AbstractParameter::class)) ? ($this->$name)->getValue() : $this->$name;
    }

    /**
     * @param \B2P\Responses\Order $orderDTO
     * @return Order|null
     */
    public function getInstance(\B2P\Responses\Order $orderDTO): ?Order
    {
        $chainHandlers = null;
        $start = &$chainHandlers;

        foreach (get_declared_classes() as $className) {
            if (in_array('B2P\Models\Interfaces\SpecificOrder', class_implements($className))) {
                try {
                    if ($start === null) {
                        $start = new $className($orderDTO, $this->client);
                        $end = $start;
                        continue;
                    }
                    $end = $end->setNext(new $className($orderDTO, $this->client));
                } catch (\throwable $e) {
                    throw new Exception(sprintf('Failed to add object %s to chain. %s', $className, $e->getMessage()));
                    // continue;
                }
            }
        }

        return ($chainHandlers) ? ($chainHandlers->getInstance($orderDTO)) : ($this->buildInstance($orderDTO));
    }

    /**
     * @param Order|null $order
     * @return Order|null
     */
    public function setNext(?Order $order): ?Order
    {
        return $this->next = $order;
    }

    /**
     * @param \B2P\Responses\Order $orderDTO
     * @return bool
     */
    public function canHandle(\B2P\Responses\Order $orderDTO): bool
    {
        return true;
    }

    /**
     * @param \B2P\Responses\Order $orderDTO
     * @return $this
     */
    public function buildInstance(\B2P\Responses\Order $orderDTO): static
    {
        foreach ($orderDTO->getParams() as $key => $value) {
            $this->$key = (is_a($value, AbstractParameter::class)) ? $value->getValue() : $value;
        }
        return $this;
    }

    /**
     * Метод получает полную сумму заказа
     *
     * @return Amount
     */
    public function getActualAmount(): Amount
    {
        return $this->amount;
    }

    /**
     * Проверяет, является ли заказ оплаченным
     *
     * @return bool
     */
    public function isPaid(): bool
    {
        if (isset($this->state) && ($this->state->name === 'COMPLETED' || $this->state->name === 'AUTHORIZED'))
            return true;
        return false;
    }

    /**
     * @return bool
     */
    public function isAuthorized(): bool
    {
        return (isset($this->state) && $this->state->name === 'AUTHORIZED');
    }

    /**
     * Метод проверяет была ли возвращена оплата по заказу
     *
     * @return bool
     */
    public function isCanceled(): bool
    {
        return (isset($this->state) && $this->state->name === 'CANCELED');
    }

    /**
     * Списание захолдированных средств по заказу
     *
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
     * Возврат оплаты по заказу
     *
     * При вызове метода происходит обращение в ПЦ с запросом о плоном возврате оплаченных по заказу средств.
     * В зависимости от успешности метод вернет или объект операции, или объект ошибки с кодом и описанием.
     *
     * @throws GuzzleException
     */
    public function reverse(): object
    {
        $data = [
            'id' => $this->id,
            'amount' => $this->amount,
            'currency' => $this->currency,
        ];
        return $this->client->reverse($data);
    }

    /**
     * Возвращает значение параметра заказа
     *
     * @param string $key
     * @return string|null
     */
    public function getParameterValue(string $key): ?string
    {
        if (isset($this->parameters) && $this->parameters['@attributes']['number']) {
            foreach ($this->parameters['parameter'] as $parameter)
                if ($parameter['name'] === $key)
                    return $parameter['value'];
        }
        return null;
    }

    /**
     * Возвращает объект операции по заказу
     *
     * @param int $operationID - идентификатор операции
     * @return object|null
     */
    public function getOperation(int $operationID): ?object
    {
        return $this->operations[$operationID] ?? null;
    }

    /**
     * Метод вернет массив операций по заказу
     *
     * @return array
     */
    public function getOperations(): array
    {
        return $this->operations ?? [];
    }

    /**
     * Метод вернет статус заказа
     *
     * @return string
     */
    public function getState(): string
    {
        return $this->state->name;
    }
}