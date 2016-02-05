<?php

namespace HipsterJazzbo\Telegraph\Services;

use HipsterJazzbo\Telegraph\Message;
use HipsterJazzbo\Telegraph\Push;
use HipsterJazzbo\Telegraph\Pushable;

interface Service
{
    public function __construct(Push $push);

    public function push(Pushable $pushable, Message $message);

    public function disconnect();
}
