<?php

namespace HipsterJazzbo\Telegraph\Tests;

use HipsterJazzbo\Telegraph\Message;
use HipsterJazzbo\Telegraph\Push;
use HipsterJazzbo\Telegraph\PushableCollection;
use HipsterJazzbo\Telegraph\Tests\Utils\TestsService;
use Mockery as m;

class PushTest extends \PHPUnit_Framework_TestCase
{
    use TestsService;

    public function tearDown()
    {
        m::close();
    }

    public function testTo()
    {
        $service = m::mock('HipsterJazzbo\Telegraph\Services\Apns');
        $service->shouldReceive('push');
        $service->shouldReceive('disconnect')->once();

        $factory = m::mock('overload:\HipsterJazzbo\Telegraph\Services\Factory');
        $factory->shouldReceive('make')->once()->andReturn($service);

        $configs = [
            'apns' => [
                'sandbox'     => true,
                'certificate' => 'certificate.pem'
            ]
        ];

        $pushable = $this->getPushableMock('apns');

        $pushables = new PushableCollection([$pushable]);

        $message = new Message('Test Body', 'Test Title');

        $push = new Push($configs);
        $push->message($message)->to($pushables);
    }
}
