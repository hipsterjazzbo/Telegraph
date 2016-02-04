<?php

namespace HipsterJazzbo\Telegraph\Services;

use HipsterJazzbo\Telegraph\Message;
use HipsterJazzbo\Telegraph\Pushable;
use RuntimeException;
use ZendService\Google\Gcm\Client;
use ZendService\Google\Gcm\Message as GcmMessage;

class Gcm implements Service
{
    /**
     * @var \ZendService\Google\Gcm\Client
     */
    private $client;

    public function __construct(array $config)
    {
        $this->client = new Client();
        $this->client->setApiKey($config['apikey']);
    }

    public function push(Pushable $pushable, Message $message)
    {
        $gcmMessage = new GcmMessage();
        $gcmMessage->addRegistrationId($pushable->getToken());
        $gcmMessage->setTitle($message->getTitle());
        $gcmMessage->setBody($message->getBody());
        $gcmMessage->setData($message->getData());

        try {
            $response = $this->client->send($gcmMessage);

            echo 'Successful: ' . $response->getSuccessCount() . PHP_EOL;
            echo 'Failures: ' . $response->getFailureCount() . PHP_EOL;
            echo 'Canonicals: ' . $response->getCanonicalCount() . PHP_EOL;
        } catch (RuntimeException $e) {
            //
        }
    }

    public function disconnect()
    {
        //
    }
}
