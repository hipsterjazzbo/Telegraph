<?php

namespace HipsterJazzbo\Telegraph\Tests;

use HipsterJazzbo\Telegraph\Services\Apns;
use HipsterJazzbo\Telegraph\Tests\Utils\TestsService;
use Mockery as m;

class ApnsTest extends \PHPUnit_Framework_TestCase
{
    use TestsService;

    public function tearDown()
    {
        m::close();
    }

    public function testApns()
    {
        $push     = $this->getPushMock();
        $pushable = $this->getPushableMock('apns');
        $message  = $this->getMessageMock();

        $response = m::mock('\ZendService\Apple\Apns\Response\Message');
        $response->shouldReceive('getCode')->andReturn(0);

        $client = m::mock('overload:ZendService\Apple\Apns\Client\Message');
        $client->shouldReceive('open')->once()->andReturn(true);
        $client->shouldReceive('send')->andReturn($response);

        $apns = new Apns($push);

        $apns->push($pushable, $message);
    }
}
