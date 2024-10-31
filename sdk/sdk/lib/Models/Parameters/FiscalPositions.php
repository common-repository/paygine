<?php

namespace B2P\Models\Parameters;

class FiscalPositions extends AbstractParameter
{
    protected string $value;

    public function __construct(array $positions)
    {
        foreach ($positions as $key => $position) {
            $positions[$key] = implode(';', $position);
        }
        $this->value = implode('|', $positions);
        return;
    }
}