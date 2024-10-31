<?php

use B2P\Client;

require_once __DIR__ . '/../vendor/autoload.php';

$client = new Client(893, 'test', true);

$client->register([
    'amount' => 17000,
    'currency' => 643,
    'description' => 'Test order',
    'reference' => '70b00286-45f7-47fe-ad27-c4c5c58ee793',
    'url' => 'http://google.com',
    'failurl' => 'http://ya.ru',
    'lang' => 'EN',
    'mode' => '1',
    'notify_customer' => '1',
    'email' => 'cms@paygine.ru',
    'phone' => '89000000000'
]);
echo "test.php".PHP_EOL;

