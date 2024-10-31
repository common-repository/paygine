<?php

namespace B2P\Models\Interfaces;

use B2P\Responses\Order;

interface SpecificOrder
{
    /**
     * @param Order $orderDTO
     * @return bool
     */
    public function canHandle(Order $orderDTO): bool;
}