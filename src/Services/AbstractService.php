<?php

namespace HipsterJazzbo\Telegraph\Services;

use HipsterJazzbo\Telegraph\Exceptions\ServiceException;
use HipsterJazzbo\Telegraph\Message;
use HipsterJazzbo\Telegraph\Push;
use HipsterJazzbo\Telegraph\Pushable;
use RuntimeException;

abstract class AbstractService implements Service
{
    /**
     * @var array Holds the config for the service
     */
    protected $config;

    /**
     * @var callable|null The callback to remove a pushable
     */
    protected $removeCallback;

    /**
     * @var callable|null The callback to update a pushable
     */
    protected $updateCallback;

    /**
     * @var int How many times the push has been tried
     */
    protected $tries = 0;

    /**
     * @var int The maximum number of times to retry a push
     */
    protected $maxTries = 3;

    /**
     * Service constructor.
     *
     * @param \HipsterJazzbo\Telegraph\Push $push
     */
    public function __construct(Push $push)
    {
        $this->config = $push->getConfig($this->getServiceName());

        $this->removeCallback = $push->getRemoveCallback();

        $this->updateCallback = $push->getUpdateCallback();

        $this->maxTries = array_get($this->config, 'retries', 3);
    }

    /**
     * Push the message to the service.
     *
     * @param \HipsterJazzbo\Telegraph\Pushable $pushable
     * @param \HipsterJazzbo\Telegraph\Message  $message
     *
     * @return mixed
     */
    public function push(Pushable $pushable, Message $message)
    {
        $serviceMessage = $this->buildServiceMessage($pushable, $message);

        try {
            $response = $this->send($serviceMessage);

            $this->handleResponse($pushable, $message, $response);
        } catch (RuntimeException $e) {
            throw new ServiceException($this->getServiceName(), 'Failed to send service message');
        }
    }

    /**
     * Retry a push that needs retrying. Should implement some mechanism
     * to prevent infinite recursion.
     *
     * @param \HipsterJazzbo\Telegraph\Pushable $pushable
     * @param \HipsterJazzbo\Telegraph\Message  $message
     *
     * @return mixed
     */
    public function retry(Pushable $pushable, Message $message)
    {
        if ($this->tries < $this->maxTries) {
            $this->tries++;

            $this->push($pushable, $message);
        } else {
            throw new ServiceException($this->getServiceName(), 'Exceeded maximum retries');
        }
    }

    /**
     * Get the service name.
     *
     * @return string
     */
    abstract protected function getServiceName();

    /**
     * Build the service-specific message from the Telegraph Message
     *
     * @param \HipsterJazzbo\Telegraph\Pushable $pushable
     * @param \HipsterJazzbo\Telegraph\Message  $message
     *
     * @return mixed
     */
    abstract protected function buildServiceMessage(Pushable $pushable, Message $message);

    /**
     * Send the prepared service message
     *
     * @param $serviceMessage
     *
     * @return mixed
     */
    abstract protected function send($serviceMessage);

    /**
     * Handle the service response. Should remove, update, or retry as appropriate.
     *
     * Should throw a ServiceException for any other errors, with an appropriate message.
     *
     * @param \HipsterJazzbo\Telegraph\Pushable $pushable
     * @param \HipsterJazzbo\Telegraph\Message  $message
     * @param mixed                             $response
     *
     * @throws \HipsterJazzbo\Telegraph\Exceptions\ServiceException
     */
    abstract protected function handleResponse(Pushable $pushable, Message $message, $response);
}
