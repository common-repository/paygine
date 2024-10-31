<?php

namespace B2P\Requests;

use B2P\Attributes\RequestParam;
use B2P\Common\Exceptions\Exception;

abstract class AbstractClientRequest extends AbstractRequest implements Interfaces\ClientPayLink
{
    abstract protected function getSignature(): string;

    public function getPayLink(): string
    {
        $url = $this->configManager->getUrl();
        $path = $this::class::PATH;
        $refClass = new \ReflectionClass($this);
        $properties = $refClass->getProperties();
        $requestParams = [];
        foreach ($properties as $property) {
            $attributes = $property->getAttributes(RequestParam::class);
            if (!empty($attributes)) {
                $requestParams[$property->getName()] = $this->{$property->getName()}->getValue();
            }
        }
        $requestParams['signature'] = $this->getSignature();

        return $url . '/' . $path . '?' . http_build_query($requestParams);
    }
}