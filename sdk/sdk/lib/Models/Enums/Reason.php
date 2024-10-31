<?php

namespace B2P\Models\Enums;

/**
 * Возможные коды ответов
 *
 * Каждой операции в ПЦ присваивается соответствующий код ответа
 */
enum Reason: int
{
    case RC1 = 1;
    case RC2 = 2;
    case RC3 = 3;
    case RC4 = 4;
    case RC5 = 5;
    case RC6 = 6;
    case RC7 = 7;
    case RC8 = 8;
    case RC9 = 9;
    case RC10 = 10;
    case RC11 = 11;
    case RC12 = 12;
    case RC13 = 13;
    case RC15 = 15;
    case RC16 = 16;
    case RC17 = 17;
    case RC18 = 18;
    case RC19 = 19;
    case RC0 = 0;

    // Reason Code Undefined
    case RCU = -1;

    /**
     * Описание на английском
     *
     * @return string
     */
    public function desc(): string
    {
        return match($this) {
            self::RC1 => 'Successful financial transaction',
            self::RC2 => 'Card expired',
            self::RC3 => 'Invalid card status',
            self::RC4 => 'Transaction declined by Issuer',
            self::RC5 => 'Invalid transaction. Declined by Issuer',
            self::RC6 => 'Insufficient funds',
            self::RC7 => 'Merchant usage limit checkup failure',
            self::RC8 => 'Antifraud checkup failure',
            self::RC9 => 'Duplicated transaction',
            self::RC10 => 'System error',
            self::RC11 => '3DS authentication failure',
            self::RC12 => 'Wrong CVV2/CVC',
            self::RC13 => 'Timeout',
            self::RC15 => 'Black list bin checkup failure',
            self::RC16 => 'Black list bin 2 checkup failure',
            self::RC17 => 'Order expired',
            self::RC18 => 'Missing month/reference',
            self::RC19 => 'The transaction is disputed by the payer. (Chargeback)',
            self::RC0 => 'Internal reason code',
            self::RCU => 'Reason Code not recognized'
        };
    }

    /**
     * Рекомендуемый текст сообщений для плательщика
     *
     * @return string
     */
    public function msg(): string
    {
        return match($this) {
            self::RC1 => 'Ваш платёж проведён успешно.',
            self::RC2, self::RC6, self::RC12 => 'Платёж отклонён. Возможные причины: недостаточно средств на счёте, были указаны неверные реквизиты карты, по Вашей карте запрещены расчёты через Интернет. Пожалуйста, попробуйте выполнить платёж повторно или обратитесь в Банк, выпустивший Вашу карту.',
            self::RC3, self::RC4, self::RC5 => 'Платёж отклонён. Пожалуйста, обратитесь в Банк, выпустивший Вашу карту.',
            self::RC7, self::RC8, self::RC9, self::RC10, self::RC11, self::RC15, self::RC16 => 'Платёж отклонён. Пожалуйста, обратитесь в Интернет-магазин.',
            self::RC13, self::RC0 => 'Платёж отклонён. Пожалуйста, попробуйте выполнить платёж позднее или обратитесь в Интернет-магазин.',
            self::RC17 => 'Время на оплату заказа истекло',
            self::RC18 => 'Неверно задан параметр \'month\'/\'reference\'',
            self::RC19 => 'Операция оспаривается плательщиком',
            self::RCU => 'Reason Code не распознан'
        };
    }
}