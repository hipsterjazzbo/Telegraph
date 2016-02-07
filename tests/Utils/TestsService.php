<?php

namespace HipsterJazzbo\Telegraph\Tests\Utils;

use Mockery as m;

trait TestsService
{
    public function getPushMock()
    {
        $push = m::mock('HipsterJazzbo\Telegraph\Push');
        $push->shouldReceive('getRemoveCallback')->andReturnNull();
        $push->shouldReceive('getUpdateCallback')->andReturnNull();
        $push->shouldReceive('getConfig')->andReturn();

        return $push;
    }

    public function getPushableMock($service)
    {
        $pushable = m::mock('HipsterJazzbo\Telegraph\Pushable');
        $pushable->shouldReceive('getToken')->andReturn(md5(1) . md5(2));
        $pushable->shouldReceive('getService')->andReturn($service);

        return $pushable;
    }

    public function getMessageMock()
    {
        $message = m::mock('HipsterJazzbo\Telegraph\Message');
        $message->shouldReceive('getTitle')->andReturn('Test Title');
        $message->shouldReceive('getBody')->andReturn('Test Body');
        $message->shouldReceive('getData')->andReturn([]);

        return $message;
    }
}
