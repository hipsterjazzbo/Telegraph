<?php

namespace HipsterJazzbo\Telegraph;

use HipsterJazzbo\Telegraph\Exceptions\InvalidServiceException;
use HipsterJazzbo\Telegraph\Exceptions\MissingMessageException;
use HipsterJazzbo\Telegraph\Exceptions\ServiceException;
use HipsterJazzbo\Telegraph\Services\Factory;
use HipsterJazzbo\Telegraph\Services\Service;
use InvalidArgumentException;

class Push
{
    /**
     * @var array
     */
    protected $configs = [];

    /**
     * @var callable
     */
    protected $removeCallback;

    /**
     * @var callable
     */
    protected $updateCallback;

    /**
     * @var bool
     */
    protected $strict;

    /**
     * @var Service[]
     */
    protected $services = [];

    /**
     * @var Message
     */
    protected $message;

    /**
     * @var PushableCollection
     */
    protected $pushables;

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

    /**
     * Make sure all services are properly disconnected
     */
    public function __destruct()
    {
        foreach ($this->services as $service) {
            $service->disconnect();
        }
    }

    /**
     * @param string|Message $message
     * @param string         $title
     *
     * @return $this
     */
    public function message($message, $title = '')
    {
        if (is_string($message)) {
            $message = new Message($message, $title);
        }

        $this->message = $message;

        return $this;
    }

    /**
     * @param PushableCollection|Pushable $pushables
     * @param bool                        $sendNow
     *
     * @return $this|bool
     */
    public function to($pushables, $sendNow = true)
    {
        if (! $this->message) {
            throw new MissingMessageException;
        }

        if ($pushables instanceof Pushable) {
            $pushables = new PushableCollection($pushables);
        }

        if (! $pushables instanceof PushableCollection) {
            throw new InvalidArgumentException;
        }

        $this->pushables = $pushables;

        if ($sendNow) {
            return $this->send();
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function send()
    {
        foreach ($this->pushables as $pushable) {
            if ($service = $this->getService($pushable->getService())) {
                try {
                    $service->push($pushable, $this->message);
                } catch (ServiceException $e) {
                    if ($this->strict) {
                        throw $e;
                    }

                    return false;
                }

                return true;
            } else {
                if ($this->strict) {
                    throw new InvalidServiceException('Missing service: ' . $pushable->getService());
                }

                return false;
            }
        }

        return true;
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
}
