<?php

namespace HipsterJazzbo\Telegraph\Services;

use HipsterJazzbo\Telegraph\Exceptions\InvalidServiceException;

class Factory
{
    private $validServices = [
        'apns',
        'gcm'
    ];

    public static function make($service)
    {
        if (! in_array($service, self::$validServices)) {
            throw new InvalidServiceException;
        }

        $adaptorClass = '\\HipsterJazzbo\\Telegraph\\Adaptors\\' . studly_case($service);

        return new $adaptorClass;
    }
}
