<?php

namespace HipsterJazzbo\Telegraph\Services;

use Exception;
use HipsterJazzbo\Telegraph\Exceptions\InvalidServiceException;
use HipsterJazzbo\Telegraph\Push;

class Factory
{
    private static $validServices = [
        'apns',
        'gcm'
    ];

    public static function make($service, Push $push)
    {
        if (! in_array($service, self::$validServices)) {
            throw new InvalidServiceException;
        }

        $serviceClass = '\\HipsterJazzbo\\Telegraph\\Services\\' . studly_case($service);

        try {
            return new $serviceClass($push);
        } catch (Exception $e) {
            throw new InvalidServiceException;
        }
    }
}
