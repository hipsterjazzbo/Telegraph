<?php

namespace HipsterJazzbo\Telegraph;

use HipsterJazzbo\Telegraph\Services\Service;
use HipsterJazzbo\Telegraph\Services\Factory;
use HipsterJazzbo\Telegraph\Exceptions\InvalidServiceException;
use InvalidArgumentException;

class Push
{
    /**
     * @var Service[]
     */
    private $services = [];

    /**
     * @var Message
     */
    private $message;

    /**
     * @var bool
     */
    private $strict;

    /**
     * @param bool $strict
     */
    public function __construct($strict = false)
    {
        $this->strict = $strict;
    }

    public function __destruct()
    {
        foreach ($this->services as $service) {
            $service->disconnect();
        }
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
            if ($adaptor = $this->getAdaptor($pushable->getService())) {
                $this->push($adaptor, $pushable, $this->message);
            }
        }
    }

    /**
     * @param string $service
     *
     * @return bool|Service
     */
    protected function getAdaptor($service)
    {
        if (! isset($this->services[$service])) {
            try {
                $this->services[$service] = Factory::make($service);
            } catch (InvalidServiceException $e) {
                if ($this->strict) {
                    throw new InvalidServiceException;
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
     */
    protected function push(Service $service, Pushable $pushable, Message $message)
    {
        $service->push($pushable, $message);
    }
}
