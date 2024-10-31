<?php

use B2P\Client;

require_once __DIR__ . '/../vendor/autoload.php';

$client = new Client(893, 'test', true);
//register
$client->complete([
    'id' => 2273750,
    'amount' => 17000,
    'currency' => 643,
]);
echo "Complete".PHP_EOL;

