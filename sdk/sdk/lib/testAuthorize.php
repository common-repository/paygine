<?php

/*
 * метод, возвращает ссылку
 *
 * 1.new Client (893...)
2.register(+фиск данные)
3. id заказа в ПЦ получили
4. authorize(id)
5. в authorize (проверяем тест или бой(меняем домен), цепляем к домену purchase,
 сигнатура по новым параметрам для authorize) формируем ссылку, возвращаем ее
 *
 * */

use B2P\Client;

require_once __DIR__ . '/../vendor/autoload.php';

$client = new Client(893, 'test', true);

$client->authorize([
    'id' => 2088773
]);

echo "authorize".PHP_EOL;

