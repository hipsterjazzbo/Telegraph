<?php

namespace HipsterJazzbo\Telegraph\Tests;

use HipsterJazzbo\Telegraph\Services\Gcm;
use HipsterJazzbo\Telegraph\Tests\Utils\TestsService;
use Mockery as m;

class GcmTest extends \PHPUnit_Framework_TestCase
{
    use TestsService;

    public function tearDown()
    {
        m::close();
    }

    public function testGcm()
    {
        $push     = $this->getPushMock();
        $pushable = $this->getPushableMock('gcm');
        $message  = $this->getMessageMock();

        $response = m::mock('\ZendService\Google\Gcm\Response');
        $response->shouldReceive('getResults')->andReturn([
            $pushable->getToken() => []
        ]);

        $client = m::mock('overload:\ZendService\Google\Gcm\Client');
        $client->shouldReceive('setApiKey')->once()->andReturn(true);
        $client->shouldReceive('send')->andReturn($response);

        $gcm = new Gcm($push);

        $gcm->push($pushable, $message);
    }
}
