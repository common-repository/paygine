<?php

namespace B2P\Requests;

use B2P\Attributes\RequestParam;
use B2P\Models\Parameters\Amount;
use B2P\Models\Parameters\Currency;
use B2P\Models\Parameters\Description;
use B2P\Models\Parameters\Email;
use B2P\Models\Parameters\FiscalPositions;
use B2P\Models\Parameters\Language;
use B2P\Models\Parameters\Mode;
use B2P\Models\Parameters\Name;
use B2P\Models\Parameters\Nonspecific;
use B2P\Models\Parameters\Phone;
use B2P\Models\Parameters\Reference;
use B2P\Models\Parameters\Signature;
use B2P\Models\Parameters\Url;

class Register extends AbstractRequest
{
    const PATH = 'webapi/Register';
    const METHOD = 'POST';

    /**
     * @var Amount Сумма заказа в минимальных единицах валюты. Для рублей — копейки
     */
    #[RequestParam(required: true)]
    protected Amount $amount;

    /**
     * @var Currency Код валюты по ISO4217
     */
    #[RequestParam(required: true)]
    protected Currency $currency;

    /**
     * @var Description Описание Заказа
     */
    #[RequestParam(required: true)]
    protected Description $description;

    /**
     * @var Reference Номер Заказа на стороне ТСП
     */
    #[RequestParam(required: false)]
    protected Reference $reference;

    /**
     * @var Url Адрес страницы на стороне ТСП, на которую по завершении Операции
     * переводится Плательщик вместо показа ему типового чека ПЦ. При переводе
     * Плательщика на адрес из url, ПЦ также передает на этот адрес идентификаторы
     * Заказа и Операции в виде GET-параметров.
     */
    #[RequestParam(required: false)]
    protected Url $url;

    /**
     * @var Url Адрес страницы на стороне ТСП, Адрес страницы на стороне ТСП, на
     * которую переводится Плательщик в случае неуспешного окончания проведения
     * Операции (когда статус Операции отличен от APPROVED, или Операция не
     * сформирована по причине какой-либо ошибки). При переводе Плательщика на
     * адрес из failurl, ПЦ также передает на него идентификаторы Заказа и Операции и код
     * ошибки (при наличии) в виде GET-параметров.
     * Если параметр failurl не указан, ПЦ считает его равным значению url.
     */
    #[RequestParam(required: false)]
    protected Url $failurl;

    /**
     * @var Mode Признак, отвечающий за необходимость отправки Плательщику email-уведомления
     * о зарегистрированном Заказе со ссылкой на оплату. Возможные значения: 0, 1.
     * 0 (значение по умолчанию) – уведомление не отправляется;
     * 1 – требуется отправить уведомление.
     */
    #[RequestParam(required: false)]
    protected Mode $notify_customer;

    /**
     * @var Email Адрес электронной почты Плательщика.
     */
    #[RequestParam(required: false)]
    protected Email $email;

    /**
     * @var Phone Телефон Плательщика.
     */
    #[RequestParam(required: false)]
    protected Phone $phone;

    /**
     * @var Mode Режим ответа на Запрос webapi/Register.
     * Возможные значения: 0 (значение по умолчанию) – полный ответ, xml-сообщение;
     * 1 – сокращенный ответ, ID зарегистрированного Заказа в формате text/plain).
     */
    #[RequestParam(required: false)]
    protected Mode $mode;

    /**
     * @var Language Язык интерфейса
     */
    #[RequestParam(required: false)]
    protected Language $lang;

    /**
     * @var Name Фамилия Имя Отчество получателя денежных средств.
     */
    #[RequestParam(required: false)]
    protected Name $fio;

    /**
     * @var $fiscal_positions FiscalPositions Фискальные позиции в формате [ [count, amount, tax, name], ... ]
     */
    #[RequestParam(required: false)]
    protected FiscalPositions $fiscal_positions;

    #[RequestParam(required: false)]
    protected Nonspecific $life_period;

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        // TODO: Implement getHeaders() method.
        return [];
    }

    protected function getSignature(): string
    {
        return Signature::make([$this->sector, $this->amount, $this->currency, $this->configManager->getPass()]);
    }
}