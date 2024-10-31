<?php

namespace B2P\Client;

//use GuzzleHttp\Client;
use B2P\Client;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;

/**
 * Отвечает за выполнение запросов к API
 *
 * @note создан на ранней стадии, вероятно будет удалён
 */
class HttpClient
{

    public function __construct(
        protected string $url
    )
    {

    }

    public function request()
    {
        $client = new Client();
//        $client->request();
    }
}