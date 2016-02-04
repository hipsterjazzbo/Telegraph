<?php

namespace HipsterJazzbo\Telegraph;

use HipsterJazzbo\Telegraph\Exceptions\InvalidServiceException;
use HipsterJazzbo\Telegraph\Services\Factory;
use HipsterJazzbo\Telegraph\Services\Service;
use InvalidArgumentException;

class Push
{
    /**
     * @var bool
     */
    private $strict;

    /**
     * @var Service[]
     */
    private $services = [];

    /**
     * @var array
     */
    private $configs = [];

    /**
     * @var Message
     */
    private $message;

    /**
     * @param array $config
     */
    public function __construct(array $config)
    {
        $this->strict  = array_get($config, 'strict', false);
        $this->configs = array_get($config, 'services', []);
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
                $this->services[$service] = Factory::make($service, array_get($this->configs, $service, []));
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
