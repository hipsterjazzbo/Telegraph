<?php

namespace HipsterJazzbo\Telegraph\Tests;

use HipsterJazzbo\Telegraph\Services\Apns;
use Mockery as m;
use ZendService\Apple\Apns\Client\Message;

class ApnsTest extends \PHPUnit_Framework_TestCase
{
    public function testApns()
    {
        $response = m::mock('\ZendService\Apple\Apns\Response\Message');
        $response->shouldReceive('getCode')->andReturn(0);

        $client = m::mock('overload:ZendService\Apple\Apns\Client\Message');
        $client->shouldReceive('open')->once()->andReturn(true);
        $client->shouldReceive('send')->andReturn($response);
        $client->shouldReceive('close')->once();

        $push = m::mock('HipsterJazzbo\Telegraph\Push');
        $push->shouldReceive('getRemoveCallback')->andReturnNull();
        $push->shouldReceive('getUpdateCallback')->andReturnNull();
        $push->shouldReceive('getConfig')->andReturn();

        $pushable = m::mock('HipsterJazzbo\Telegraph\Pushable');
        $pushable->shouldReceive('getToken')->andReturn(md5(1).md5(2));

        $message = m::mock('HipsterJazzbo\Telegraph\Message');
        $message->shouldReceive('getTitle')->andReturn('Test Title');
        $message->shouldReceive('getBody')->andReturn('Test Body');
        $message->shouldReceive('getData')->andReturn([]);

        $apns = new Apns($push);

        $apns->push($pushable, $message);
    }
}
