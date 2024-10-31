<?php

namespace B2P\Common\Exceptions;

use Throwable;

abstract class AbstractTestException extends \LogicException
{
    protected string $log_prefix = 'paygine payment sdk: ';

    public function __construct($message = "", $code = 0, Throwable $previous = null)
    {
        $message = '<b>' . $this->log_prefix . $message . '</b>' . "\n\n" .
            print_r($this, true) . "\n\n";
            // (($previous) ? (print_r($previous, true)) : '');
        error_log($message);
        if (ini_get('display_errors') === "On") {
            echo '<pre>' . $message . '</pre>';
        }
    }
}
