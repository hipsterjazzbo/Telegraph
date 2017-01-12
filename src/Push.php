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
    protected $serviceConfigs = [];

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
     * @param array $serviceConfigs
     * @param bool  $strict
     */
    public function __construct(array $serviceConfigs, $strict = false)
    {
        $this->serviceConfigs = $serviceConfigs;

        $this->strict = $strict;
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
     * @return bool|self
     */
    public function to($pushables, $sendNow = true)
    {
        if (!$this->message) {
            throw new MissingMessageException;
        }

        if ($pushables instanceof Pushable) {
            $pushables = new PushableCollection($pushables);
        }

        if (!$pushables instanceof PushableCollection) {
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
        $success = true;

        foreach ($this->pushables as $pushable) {
            if ($service = $this->getService($pushable->getService())) {
                try {
                    $service->push($pushable, $this->message);
                } catch (ServiceException $e) {
                    if ($this->strict) {
                        throw $e;
                    }

                    $success = false;
                }
            } else {
                if ($this->strict) {
                    throw new InvalidServiceException('Missing service: '.$pushable->getService());
                }

                $success = false;
            }
        }

        $this->disconnect();

        return $success;
    }

    /**
     * @param string $service
     *
     * @return array
     */
    public function getConfig($service)
    {
        if (!array_key_exists($service, $this->serviceConfigs)) {
            throw new InvalidServiceException;
        }

        return $this->serviceConfigs[$service];
    }

    /**
     * @param string $service
     *
     * @return bool|Service
     */
    protected function getService($service)
    {
        if (!isset($this->services[$service])) {
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
     * Make sure all services are properly disconnected
     */
    protected function disconnect()
    {
        foreach ($this->services as $service) {
            $service->disconnect();
        }
    }
}
