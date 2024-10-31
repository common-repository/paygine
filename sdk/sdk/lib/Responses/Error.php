<?php

namespace B2P\Responses;

use B2P\Attributes\ResponseParam;
use B2P\Models\Parameters\Description;
use B2P\Models\Parameters\Nonspecific;

/**
 * Объект ошибки в ПЦ
 * <description> — текстовое сообщение об ошибке;
 * <code> — код ошибки.
 */
class Error extends AbstractResponse
{
    /**
     * @var Description Текстовое сообщение об ошибке
     */
    #[ResponseParam]
    protected Description $description;

    /**
     * @var Nonspecific Код ошибки
     */
    #[ResponseParam]
    protected Nonspecific $code;

}