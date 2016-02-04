<?php

namespace HipsterJazzbo\Telegraph\Services;

use HipsterJazzbo\Telegraph\Message;
use HipsterJazzbo\Telegraph\Pushable;

interface Service
{
    public function __construct(array $config);

    public function push(Pushable $pushable, Message $message);

    public function disconnect();
}
