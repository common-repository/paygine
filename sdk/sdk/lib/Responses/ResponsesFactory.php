<?php

namespace B2P\Responses;

use B2P\Common\ConfigManager;
use B2P\Models\Parameters\ParametersFactory;
use B2P\Models\Parameters\Signature;
use B2P\Responses\Operation as Operation;
use B2P\Responses\Order as Order;
use Exception;
use LogicException;
use UnhandledMatchError;

/**
 * Фабрика ответов
 *
 * Отвечает за создание DTO ответов
 */
class ResponsesFactory
{
    /**
     * Разобрать сырой объект запроса
     *
     * @param $response
     * @return ?AbstractResponse
     * @throws Exception
     */
    public static function parseResponse($response): ?AbstractResponse
    {
        $responseObject = json_decode($response);

        if ($responseObject === null) {
            $xml = simplexml_load_string($response);
            foreach($xml->xpath('//*[not(text())]') as $torm)
                unset($torm[0]);
            $responseType = $xml->getName();
            $json = json_encode($xml);
            $responseFields = json_decode($json, TRUE);
        } else {
            throw new Exception('Building response object failed. JSON format is unsupported');
            // $responseArray = get_object_vars($responseObject);
            // $responseType = array_key_first($responseArray);
            // $responseFields = get_object_vars($responseArray[$responseType]);
        }

        if (self::signatureIsCorrect($responseFields) === false)
            throw new Exception('Building response object failed. Signature incorrect');

        return self::make($responseType, $responseFields);
    }

    /**
     * @param string $type
     * @param array $data
     * @return ?AbstractResponse
     */
    public static function make(string $type, array $data): ?AbstractResponse
    {
        $configManager = ConfigManager::getInstance();

        try {
            $response = match (strtolower($type)) {
                'order' => new Order($configManager),
                'operation' => new Operation($configManager),
                'error' => new Error($configManager),
                default => throw new \B2P\Common\Exceptions\Exception('Unknown type of response'),
            };
            foreach ($data as $paramName => $paramValue) {
                if (is_array($paramValue)) {
                    $response->{$paramName} = $paramValue;
                    continue;
                }
                $param = ParametersFactory::make($paramName, $paramValue, $type);
                $response->setParam($paramName, $param);
            }
        } catch (UnhandledMatchError) {
            throw new LogicException("Attempting to create a query of a non-existent type: $type");
        }

        return $response ?? null;
    }

    /**
     * @param array $params
     * @param string $signature
     * @return bool
     */
    public static function signatureIsCorrect(array $params, string $signature = ''): bool
    {
        $configManager = ConfigManager::getInstance();
        if (isset($params['signature'])) {
            $signature = $params['signature'];
            unset($params['signature']);
        }
        $params['password'] = $configManager->getPass();
        if ($signature == Signature::make($params) || $signature === '')
            return true;
        return false;
    }

}