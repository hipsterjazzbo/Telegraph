<?php

namespace HipsterJazzbo\Telegraph\Tests;

use HipsterJazzbo\Telegraph\Message;
use HipsterJazzbo\Telegraph\Push;
use HipsterJazzbo\Telegraph\PushableCollection;
use Mockery as m;

class PushTest extends \PHPUnit_Framework_TestCase
{
    public function testTo()
    {
        $service = m::mock('HipsterJazzbo\Telegraph\Services\Apns');
        $service->shouldReceive('push');
        $service->shouldReceive('disconnect')->once();

        $factory = m::mock('overload:\HipsterJazzbo\Telegraph\Services\Factory');
        $factory->shouldReceive('make')->once()->andReturn($service);

        $configs = [
            'sandbox'     => true,
            'certificate' => 'certificate.pem'
        ];

        $pushable = m::mock('HipsterJazzbo\Telegraph\Pushable');
        $pushable->shouldReceive('getToken')->andReturn('1');
        $pushable->shouldReceive('getService')->andReturn('apns');

        $pushables = new PushableCollection([$pushable]);

        $message = new Message('Test Title', 'Test Body');

        $push = new Push($configs);
        $push->message($message)->to($pushables);
    }
}
