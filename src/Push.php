<?php

namespace HipsterJazzbo\Telegraph;

use HipsterJazzbo\Telegraph\Exceptions\InvalidServiceException;
use HipsterJazzbo\Telegraph\Exceptions\ServiceException;
use HipsterJazzbo\Telegraph\Services\Factory;
use HipsterJazzbo\Telegraph\Services\Service;
use InvalidArgumentException;

class Push
{
    /**
     * @var array
     */
    private $configs = [];

    /**
     * @var callable
     */
    private $removeCallback;

    /**
     * @var callable
     */
    private $updateCallback;

    /**
     * @var bool
     */
    private $strict;

    /**
     * @var Service[]
     */
    private $services = [];

    /**
     * @var Message
     */
    private $message;

    /**
     * @param array    $configs
     * @param callable $remove
     * @param callable $update
     * @param bool     $strict
     */
    public function __construct(array $configs, callable $remove = null, callable $update = null, $strict = false)
    {

        $this->configs        = $configs;
        $this->removeCallback = $remove;
        $this->updateCallback = $update;
        $this->strict         = $strict;
    }

    public function __destruct()
    {
        foreach ($this->services as $service) {
            $service->disconnect();
        }
    }

    /**
     * @param string $service
     *
     * @return array
     */
    public function getConfig($service)
    {
        if (! array_key_exists($service, $this->configs)) {
            throw new InvalidServiceException;
        }

        return $this->configs[$service];
    }

    /**
     * @return callable
     */
    public function getRemoveCallback()
    {
        return $this->removeCallback;
    }

    /**
     * @return callable
     */
    public function getUpdateCallback()
    {
        return $this->updateCallback;
    }

    /**
     * @param Message $message
     *
     * @return $this
     */
    public function message(Message $message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @param PushableCollection|Pushable $pushables
     */
    public function to($pushables)
    {
        if ($pushables instanceof Pushable) {
            $pushables = new PushableCollection($pushables);
        }

        if (! $pushables instanceof PushableCollection) {
            throw new InvalidArgumentException;
        }

        foreach ($pushables as $pushable) {
            if ($service = $this->getService($pushable->getService())) {
                $this->push($service, $pushable, $this->message);
            }
        }
    }

    /**
     * @param string $service
     *
     * @return bool|Service
     */
    protected function getService($service)
    {
        if (! isset($this->services[$service])) {
            try {
                $this->services[$service] = Factory::make($service, $this);
            } catch (InvalidServiceException $e) {
                if ($this->strict) {
                    throw $e;
                }

                return false;
            }
        }

        return $this->services[$service];
    }

    /**
     * @param Service  $service
     * @param Pushable $pushable
     * @param Message  $message
     *
     * @return array
     */
    protected function push(Service $service, Pushable $pushable, Message $message)
    {
        try {
            $service->push($pushable, $message);
        } catch (ServiceException $e) {
            if ($this->strict) {
                throw $e;
            }

            return false;
        }

        return true;
    }
}
