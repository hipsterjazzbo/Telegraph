<?php

namespace HipsterJazzbo\Telegraph\Services;

use HipsterJazzbo\Telegraph\Message;
use HipsterJazzbo\Telegraph\Push;
use HipsterJazzbo\Telegraph\Pushable;

interface Service
{
    /**
     * Service constructor.
     *
     * @param \HipsterJazzbo\Telegraph\Push $push
     */
    public function __construct(Push $push);

    /**
     * Push the message to the service.
     *
     * @param \HipsterJazzbo\Telegraph\Pushable $pushable
     * @param \HipsterJazzbo\Telegraph\Message  $message
     *
     * @return mixed
     */
    public function push(Pushable $pushable, Message $message);

    /**
     * Retry a push that needs retrying. Should implement some mechanism
     * to prevent infinite recursion.
     *
     * @param \HipsterJazzbo\Telegraph\Pushable $pushable
     * @param \HipsterJazzbo\Telegraph\Message  $message
     *
     * @return mixed
     */
    public function retry(Pushable $pushable, Message $message);

    /**
     * Handle any disconnection or cleanup that the service may require
     *
     * @return mixed
     */
    public function disconnect();
}
