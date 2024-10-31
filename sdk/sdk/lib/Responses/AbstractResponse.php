<?php

namespace B2P\Responses;

use B2P\Common\ConfigManager;
use B2P\Models\Parameters\ParameterInterface;
use B2P\Requests\AbstractRequest;
use Exception;
use http\Exception\InvalidArgumentException;
use LogicException;

abstract class AbstractResponse
{

    protected ConfigManager $configManager;

    /**
     * @var string[] Ассоциативный массив соотносящий названия параметров запроса и названия свойств
     * @todo реализовать всю смежную логику по подобию с запросами {@see AbstractRequest::generateParamsIndex()}
     */
    protected array $paramsIndex;

    public function __construct(ConfigManager $configManager)
    {
        $this->configManager = $configManager;
    }

    public function __get(string $name)
    {
        return property_exists($this, $name) && isset($this->$name) ? $this->$name : null;
    }

    public function __set(string $name, $val)
    {
        try {
            $this->checkProperty($name, $val);
        } catch (\InvalidArgumentException $e) {
        }
    }

    public function checkProperty(string $name, $val)
    {
        $prop_exist = false;
        $prop_exist = property_exists($this, strtolower($name));

        if ($prop_exist) {
            $this->$name = $val;
        } else {
            throw new \InvalidArgumentException("Undefined Property: $name");
        }
    }

    /**
     * Получить параметр ответа
     *
     * @param string $param_name
     * @return ParameterInterface|null
     */
    public function getParam(string $param_name): ?ParameterInterface
    {
        //TODO: реализовать

        return null;
    }

    /**
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

    public function getParams(): array
    {
        return get_object_vars($this);
    }

    /**
     * Установить параметр ответа
     *
     * @param string $param_name
     * @param ParameterInterface $value
     * @return AbstractResponse
     * @throws LogicException при попытке установить несуществующий параметр
     */
    public function setParam(string $param_name, ParameterInterface $value): self
    {
        $this->$param_name = $value;
        return $this;
    }


    public function __toString()
    {
        $config_origin = clone $this->configManager;
        $config_masked = clone $this->configManager;
        $config_masked->setPass('**********');

        $this->configManager = $config_masked;
        $result = clone $this;
        $this->configManager = $config_origin;

        return print_r($result, true);
    }


}