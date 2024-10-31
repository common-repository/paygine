<?php

use B2P\Client;

require_once __DIR__ . '/../vendor/autoload.php';

$client = new Client(893, 'test', true);

$client->ping(null);
echo "testPing.php".PHP_EOL;

