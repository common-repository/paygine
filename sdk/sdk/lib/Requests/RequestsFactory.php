<?php

namespace B2P\Requests;

use B2P\Common\ConfigManager;
use B2P\Models\Parameters\ParametersFactory;
use LogicException;

//use UnhandledMatchError;

/**
 * Фабрика запросов
 *
 * Отвечает за создание DTO запросов
 */
class RequestsFactory
{
    /**
     * Создать объект запроса
     *
     * @param string $requestType тип запроса, например 'register' или 'webapi/register'
     * @param array $data массив параметров запроса
     * @param ConfigManager|null $configManager объект конфигураций SDK
     * @return AbstractRequest
     * @todo тип возвращаемого значения должен декларироваться по интерфейсу запроса,
     *       а не по абстрактному классу. Интерфейс пока не создан.
     */
    public static function make(string $requestType, array $data, ?ConfigManager $configManager = null): AbstractRequest
    {
        if (is_null($configManager))
            $configManager = ConfigManager::getInstance();

        try {
            $requestType = 'B2P\\Requests\\' . ucfirst($requestType);
            if (class_exists($requestType)) {
                $request = new $requestType($configManager, $data);
            } else {
                throw new \Exception('Method \'' . $requestType . '\' is not exist');
            }

            foreach ($data as $paramName => $paramValue) {
                $param = ParametersFactory::make($paramName, $paramValue);
                $request->setParam($paramName, $param);
            }
        } catch (\Exception $e) {
            throw new LogicException(($e->getMessage()) ? $e->getMessage() : "Attempting to create a query of a non-existent type: $requestType");
        }

        return $request;
    }
}