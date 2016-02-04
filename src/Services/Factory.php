<?php

namespace HipsterJazzbo\Telegraph\Services;

use Exception;
use HipsterJazzbo\Telegraph\Exceptions\InvalidServiceException;

class Factory
{
    private $validServices = [
        'apns',
        'gcm'
    ];

    public static function make($service, $config = [])
    {
        if (! in_array($service, self::$validServices)) {
            throw new InvalidServiceException;
        }

        $serviceClass = '\\HipsterJazzbo\\Telegraph\\Services\\' . studly_case($service);

        try {
            return new $serviceClass($config);
        } catch (Exception $e) {
            throw new InvalidServiceException;
        }
    }
}
