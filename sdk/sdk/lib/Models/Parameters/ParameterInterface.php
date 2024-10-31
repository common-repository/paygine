<?php

namespace B2P\Models\Parameters;

interface ParameterInterface
{
    public function __toString(): string;
    public function getValue();
}