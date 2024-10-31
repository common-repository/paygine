<?php

namespace B2P;

use B2P\Attributes\PaymentMethodName;
use B2P\Attributes\PaymentMethod;
use B2P\Common\ConfigManager;
use B2P\Models\Orders\Order;
use B2P\Requests\AbstractClientRequest;
use B2P\Requests\AbstractRequest;
use B2P\Requests\RequestsFactory;
use B2P\Responses\Error;
use B2P\Responses\Operation;
use B2P\Responses\ResponsesFactory;
use B2P\Services\AmountService;
use Exception;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Exception\GuzzleException;
use LogicException;
use Psr\Http\Message\ResponseInterface;

//use Psr\Http\Client\ClientInterface as HttpClient;
//use Psr\Http\Message\RequestInterface;

/**
 * Основной клиент
 *
 * @note Находится на этапе активной разработки. Структура не продумана и может кардинально меняться с каждым коммитом.
 */
class Client
{
    /**
     * @var ConfigManager Объект конфигурации SDK. Сам клиент явно не содержит сектор, пароль и другие настройки.
     */
    protected ConfigManager $configManager;

    /**
     * @var HttpClient HTTP клиент, через который отправляются запросы на API
     */
    protected HttpClient $httpClient;

    public function __construct(int $sector, string $pass, bool $testMode = false, $sha256 = false)
    {
        $this->configManager = ConfigManager::getInstance()
            ->setSector($sector)
            ->setPass($pass)
            ->setTestMode($testMode);
        if ($sha256)
            $this->configManager->setSHA256();

        // это временное решение. Сделано так только для простоты.
        // После внедрения PSR-18 будет переделано на работу с любым клиентом.
        // Но однозначно объект клиента также будет сохранён в свойстве.
        $this->httpClient = new HttpClient([
            'base_uri' => $this->configManager->getUrl(),
        ]);
    }

    /**
     * @return bool|ConfigManager
     */
    public function getConfigManager(): bool|ConfigManager
    {
        return $this->configManager ?? false;
    }

    /**
     * Регистрация заказа
     *
     * Метод используется для регистрации Заказа в ПЦ.
     * Возвращает объект зарегистрированного заказа или объект ошибки.
     *
     * @param array $data массив параметров запроса
     * @return Order|Error
     * @throws GuzzleException
     */
    public function register(array $data): Order|Error
    {
        $request = RequestsFactory::make('register', $data);
        $result = $this->sendRequest($request);
        return $this->handleResponse($result->getBody());
    }


    /**
     * Отслеживание доступности
     *
     * Метод используется для отслеживания доступности сервиса. Если сервис доступен вернет строку 'Pong'
     *
     * @return object
     * @throws GuzzleException
     * @throws Exception
     */
    public function ping(): object
    {
        $request = RequestsFactory::make('ping', []);
        return $this->sendRequest($request)->getBody();
    }

    /**
     * Получение информации по заказу
     *
     * В массиве `$data` необходимо передать `id` заказа с ключом "id":
     *
     * ```['id' => 123]```
     *
     * Метод вернет объект заказа со всеми параметрами и операциями.
     *
     * @param array $data ID заказа
     * @return Order|Error
     * @throws GuzzleException
     */
    public function order(array $data): Order|Error
    {
        $request = RequestsFactory::make('order', $data);
        $result = $this->sendRequest($request);
        return $this->handleResponse($result->getBody());
    }

    /**
     * Получение информации об операции
     *
     * В массиве `$data` необходимо передать `id` заказа с ключом "id" и `id` операции с ключом "operation":
     *
     * ```['id' => 123, 'operation' => 12345]```
     *
     * @param array $data ID операции
     * @return Operation|Error
     * @throws GuzzleException
     */
    public function operation(array $data): Operation|Error
    {
        $request = RequestsFactory::make('operation', $data);
        $result = $this->sendRequest($request);
        return $this->handleResponse($result->getBody());
    }

    /**
     * Получение ссылки на оплату
     *
     * @param string $method - название метода оплаты
     * @param array $data - параметры
     * @return string
     */
    public function getPaymentLink(string $method, array $data): string
    {
        $request = RequestsFactory::make($method, $data);
        if (is_a($request, AbstractClientRequest::class))
            return $request->getPayLink();
        throw new Common\Exceptions\Exception('Failed to create payment link for method ' . $method);
    }

    /**
     * Холдирование средств на карте
     *
     * Метод вернет клиентскую платежную ссылку для оплаты заказа методом `AUTHORIZE` (холдирование без списания)
     *
     * @param array $data ID заказа
     * @return string
     */
    #[PaymentMethod]
    #[PaymentMethodName(
        [
            'RU' => 'Двухстадийная оплата',
            'EN' => 'Two-step payment'
        ]
    )]
    public function authorize(array $data): string
    {
        return $this->getPaymentLink('authorize', $data);
    }

    /**
     * Одностадийная оплата по карте
     *
     * Метод вернет клиентскую платежную ссылку для оплаты заказа методом `PURCHASE` (со списанием)
     *
     * @param array $data ID заказа
     * @return string
     */
    #[PaymentMethod]
    #[PaymentMethodName(
        [
            'RU' => 'Одностадийная оплата',
            'EN' => 'One-step payment'
        ]
    )]
    public function purchase(array $data): string
    {
        return $this->getPaymentLink('purchase', $data);
    }

    /**
     * Оплата по qr-коду СБП, прямая ссылка
     *
     * @param array $data ID операции
     * @return string
     */
    #[PaymentMethod]
    #[PaymentMethodName(
        [
            'RU' => 'Оплата по QR-коду СБП',
            'EN' => 'Purchase by QR'
        ]
    )]
    public function purchaseSBP(array $data): string
    {
        return $this->getPaymentLink('purchaseSBP', $data);
    }

    /**
     * Одностадийная рассрочка Плайт (Халва)
     *
     * @param array $data
     * @return string
     */
    #[PaymentMethod]
    #[PaymentMethodName(
        [
            'RU' => 'Одностадийная рассрочка Плайт',
            'EN' => 'Plait PWI'
        ]
    )]
    public function purchaseWithInstallment(array $data): string
    {
        return $this->getPaymentLink('purchaseWithInstallment', $data);
    }

    /**
     * Двухстадийная рассрочка Плайт (Халва)
     *
     * @param array $data
     * @return string
     */
    #[PaymentMethod]
    #[PaymentMethodName(
        [
            'RU' => 'Двухстадийная рассрочка Плайт',
            'EN' => 'Plait AWI'
        ]
    )]
    public function authorizeWithInstallment(array $data): string
    {
        return $this->getPaymentLink('authorizeWithInstallment', $data);
    }

    /**
     * Рассрочка Подели
     *
     * @param array $data
     * @return string
     *
     */
    #[PaymentMethod]
    #[PaymentMethodName(
        [
            'RU' => 'Рассрочка "Подели"',
            'EN' => 'Podeli PWI'
        ]
    )]
    public function podeliPWI(array $data): string
    {
        return $this->getPaymentLink('podeliPWI', $data);
    }

    /**
     * Покупка в кредит, вернет ссылку на форму для прохождения скорринга
     *
     * @param $data
     * @return string
     */
    #[PaymentMethod]
    #[PaymentMethodName(
        [
            'RU' => 'Кредитование',
            'EN' => 'Loan'
        ]
    )]
    public function loan($data): string
    {
        return $this->getPaymentLink('loan', $data);
    }

    /**
     * Списание захолдированных средств
     *
     * @param $data
     * @return bool
     * @throws GuzzleException
     * @throws Exception
     */
    public function complete($data): bool
    {
        $request = RequestsFactory::make('complete', $data);
        $result = $this->sendRequest($request);
        $parsingResponse = $this->handleResponse($result->getBody());

        return !is_a($parsingResponse, 'B2P\Responses\Error');
    }

    /**
     * Возврат оплаты по заказу
     *
     * @param $data
     * @return object
     * @throws GuzzleException
     * @throws Exception
     */
    public function reverse($data): object
    {
        $request = RequestsFactory::make('reverse', $data);
        $result = $this->sendRequest($request);

        return $this->handleResponse($result->getBody());
    }

    /**
     * Одностадийная оплата
     *
     * @param array $data Массив параметров для регистрации заказа. Обязательные - amount (сумма), currency (валюта), description
     * @return false|string
     * @throws GuzzleException
     */
    public function oneStepPayment(array $data): bool|string
    {
        $reg_result = $this->register($data);
        if (!is_a($reg_result, 'B2P\Responses\Error') && isset($reg_result->id)) {
            return $this->purchase(['id' => $reg_result->id]);
        } else if (is_a($reg_result, 'B2P\Responses\Error')) {
            throw new LogicException($reg_result->description, 192);
        } else if ((int)$reg_result > 0) {
            return $this->purchase(['id' => (int)$reg_result]);
        } else return false;
    }

    /**
     * Отправка запроса
     *
     * @throws GuzzleException
     */
    public function sendRequest(AbstractRequest $request): ResponseInterface
    {
        return $this->httpClient->request($request::METHOD, $request::PATH, [
            'form_params' => $request->getBody()
        ]);
    }

    /**
     * Обработка ответа
     *
     * Сами запросы будут осуществляться через любой сторонний HTTP клиент.
     * После того как разберёмся с PSR будет установленно какой тип имеет $result и как с ним работать.
     *
     * В этом методе нужно будет вытащить основные данные по запросу:
     * - это успешно ли выполнен запрос,
     * - какой код ответа
     * - cам ответ нужно сохранить, чтобы ещё раз его получить, например, в обработчике ошибок для логирования
     *
     * @param $result
     * @return object
     * @throws Exception
     */
    public function handleResponse($result): object
    {
        // само тело ответа следует отправить в фабрику для создания подходящего объекта ответа.
        $dto = ResponsesFactory::parseResponse($result);
        $modelName = 'B2P\\Models\\' . explode('\\', $dto::class)[2] . 's\\' . explode('\\', $dto::class)[2];
        if (class_exists($modelName)) {
            $baseInstance = (new $modelName($dto, $this));
            return ($baseInstance->specificInstance) ?? $baseInstance;
        }
        return $dto;
    }

    /**
     * Конвертация рублей в копейки
     *
     * @throws Exception
     */
    public function centifyAmount($amount): int
    {
        return AmountService::centifyAmount($amount);
    }

    /**
     * Возвращает список доступных в текущей версии SDK методов оплаты
     *
     * Список методов может использоваться модулем для отображения в настройках платежной системы в cms.
     *
     * Для добавления нового метода оплаты, его необходимо отметить атрибутом `#[PaymentMethod]`.
     *
     * Для указания имени метода, необходимо создать у него атрибут <br>`#[PaymentMethodName(['LANG_CODE' => 'NAME', ... ])]`.
     *
     * @param string|null $lang - строковый код языка имени метода ['RU'|'EN'], при отсутствии вернутся значения для всех языков
     * @param bool $CP1251 - при включении выведет значение имени метода в кодировке CP1251
     * @return array - ассоциированный массив вида <br>`['method' => 'Method Name', ... ]`
     */
    public static function getPaymentMethods(?string $lang = null, bool $CP1251 = false): array
    {
        $methods = [];
        $reflectionClass = new \ReflectionClass(self::class);
        foreach ($reflectionClass->getMethods() as $method) {
            $attributes = $method->getAttributes(PaymentMethod::class);
            if (!empty($attributes)) {
                $paymentMethodName = $method->getAttributes(PaymentMethodName::class);
                $methods[$method->getName()] = (!empty($paymentMethodName) && $names = $paymentMethodName[0]->getArguments()[0]) ?
                    (($lang && isset($names[$lang])) ?
                        (($CP1251) ?
                            mb_convert_encoding($names[$lang], 'windows-1251', 'utf-8') :
                            $names[$lang]
                        ) :
                        (($CP1251) ?
                            array_map(function ($element) {
                                return mb_convert_encoding($element, 'windows-1251', 'utf-8');
                            }, $names) :
                            $names
                        )
                    ) : $method->getName();
            }
        }
        return $methods;
    }
}