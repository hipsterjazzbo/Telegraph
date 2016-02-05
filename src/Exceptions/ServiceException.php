<?php

namespace HipsterJazzbo\Telegraph\Exceptions;

use Exception;
use RuntimeException;

class ServiceException extends RuntimeException
{
    /**
     * @var string
     */
    private $service;

    public function __construct($service, $message, $code = null, Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);

        $this->service = $service;
    }

    /**
     * @return string
     */
    public function getService()
    {
        return $this->service;
    }
}
