<?php

namespace B2P\Requests;

use B2P\Attributes\RequestParam;
use B2P\Common\ConfigManager;
use B2P\Common\Exceptions\BadRequestParamException;
use B2P\Models\Parameters\ParameterInterface;
use B2P\Models\Parameters\Sector;
use Exception;
use ReflectionObject;
use B2P\Requests\Traits\ClientPayLink;

/**
 * Запрос к ПЦ
 *
 * Содержит все необходимые параметры запроса.
 * Содержит методы генерации данных для http запроса.
 */
abstract class AbstractRequest
{
    /**
     * Часть url path, на которую нужно отправлять текущий запрос
     */
    const PATH = '';
    const METHOD = 'POST';

    #[RequestParam(required: true)]
    protected Sector $sector;
    protected ConfigManager $configManager;

    protected array $requiredParams = [];

    /**
     * @var string[] Ассоциативный массив соотносящий названия параметров запроса и названия свойств
     */
    protected array $paramsIndex;

    public function __construct(ConfigManager $configManager, $params = [])
    {
        $this->sector = $configManager->getSector();
        $this->configManager = $configManager;
        $this->generateParamsIndex();
        $this->setRequiredParams();

        if (count($params))
            $this->checkParams($params);
    }

    /**
     * @throws Exception
     */
    public function checkParams(array $params): void
    {
        foreach ($this->requiredParams as $key)
            if (!isset($params[$key]))
                throw new Exception('Required parameter \'' . $key . '\' not specified.' . PHP_EOL .
                    'Required params for ' . $this::class . ': ' .
                    print_r(json_encode($this->requiredParams), true));
    }

    /**
     * Получить заголовки запроса
     *
     * @return string[]
     */
    public function getHeaders(): array
    {
        return [];
    }

    /**
     * Получить тело запроса
     *
     * Собирает тело запроса из свойств, которые являются параметрами запроса и генерирует сигнатуру
     *
     * @return array
     */
    public function getBody(): array
    {
        $body = [];

        $reflection_obj = new ReflectionObject($this);
        foreach ($reflection_obj->getProperties() as $reflection_property) {
            $key = $reflection_property->getName();
            $reflection_property->setAccessible(1);
            if (($reflection_property->isInitialized($this))) {
                if (is_object($reflection_property->getValue($this)) && method_exists($reflection_property->getValue($this), '__toString'))
                    $body[$key] = (string)$reflection_property->getValue($this);
            }
        }

        $body['signature'] = $this->getSignature();

        return $body;
    }

    /**
     * Сгенерировать сигнатуру запроса
     *
     * @return string
     */
    abstract protected function getSignature(): string;

    /**
     * Сгенерировать индекс для параметров запроса
     *
     * @return void
     */
    protected function generateParamsIndex(): void
    {
        $reflection_obj = new ReflectionObject($this);
        foreach ($reflection_obj->getProperties() as $reflection_property) {
            $reflection_attributes = $reflection_property->getAttributes(RequestParam::class);
            if (count($reflection_attributes) > 0) {
                $param_name = $reflection_attributes[0]->newInstance()->paramName ?: $reflection_property->getName();
                $this->paramsIndex[$param_name] = $reflection_property->getName();
            }
        }
    }

    /**
     * Метод формирует массив обязательных параметров запроса (requiredParams)
     * по атрибутам свойств (RequestParam(required: true))
     * и сохраняет его в свойстве
     *
     * @return void
     */
    protected function setRequiredParams(): void
    {
        $required_params = [];
        $reflection_obj = new ReflectionObject($this);
        foreach ($reflection_obj->getProperties() as $reflection_property) {
            $param_name = $reflection_property->name;
            if ($param_name === 'sector') continue;
            $reflection_attributes = $reflection_property->getAttributes(RequestParam::class);
            if (count($reflection_attributes) > 0) {
                for ($i=0; $i<count($reflection_attributes); $i++) {
                    $required_param = $reflection_attributes[$i]->getArguments()['required'] ?? false;
                    if ($required_param) {
                        $required_params[] = $param_name;
                        break;
                    }
                }
            }
        }
        $this->requiredParams = $required_params;
    }

    /* -- getters -- */

    public function __get(string $name)
    {
        return property_exists($this, $name) && isset($this->$name) ? $this->$name : null;
    }

    /**
     * Получить параметр запроса
     *
     * @param string $param_name
     * @return ParameterInterface|null
     */
    public function getParam(string $param_name): ?ParameterInterface
    {
        if (isset($this->paramsIndex[$param_name])) {
            $name = $this->paramsIndex[$param_name];
            return $this->$name ?? null;
        }

        return null;
    }

    /* -- setters -- */

    /**
     * Установить параметр запроса
     *
     * @param string $param_name
     * @param ParameterInterface $value
     * @return AbstractRequest
     * @throws BadRequestParamException при попытке установить несуществующий параметр
     */
    public function setParam(string $param_name, ParameterInterface $value): self
    {
        if (isset($this->paramsIndex[$param_name])) {
            $name = $this->paramsIndex[$param_name];
            $this->$name = $value;
        } else {
            throw new BadRequestParamException(sprintf(
                'Attempting to set an unsupported parameter(%s) for a %s request',
                $param_name, static::Class));
        }

        return $this;
    }
}