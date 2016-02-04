<?php

namespace HipsterJazzbo\Telegraph\Services;

use HipsterJazzbo\Telegraph\Message;
use HipsterJazzbo\Telegraph\Pushable;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use ZendService\Apple\Apns\Client\Message as Client;
use ZendService\Apple\Apns\Message as ApnsMessage;
use ZendService\Apple\Apns\Message\Alert;
use ZendService\Apple\Apns\Response\Message as Response;

class Apns implements Service
{
    /**
     * @var \ZendService\Apple\Apns\Client\Message
     */
    private $client;

    public function __construct(array $config)
    {
        $this->client = new Client;
        $this->client->open(Client::SANDBOX_URI, $config['certificate'], $config['passphrase']);
    }

    public function push(Pushable $pushable, Message $message)
    {
        $alert = new Alert();
        $alert->setTitle($message->getTitle());
        $alert->setBody($message->getBody());

        $apnsMessage = new ApnsMessage();
        $apnsMessage->setId(Uuid::uuid4());
        $apnsMessage->setToken($pushable->getToken());
        $apnsMessage->setAlert($alert);
        $apnsMessage->setCustom($message->getData());

        try {
            $response = $this->client->send($message);

            if ($response->getCode() != Response::RESULT_OK) {
                switch ($response->getCode()) {
                    case Response::RESULT_PROCESSING_ERROR:
                        // you may want to retry
                        break;
                    case Response::RESULT_MISSING_TOKEN:
                        // you were missing a token
                        break;
                    case Response::RESULT_MISSING_TOPIC:
                        // you are missing a message id
                        break;
                    case Response::RESULT_MISSING_PAYLOAD:
                        // you need to send a payload
                        break;
                    case Response::RESULT_INVALID_TOKEN_SIZE:
                        // the token provided was not of the proper size
                        break;
                    case Response::RESULT_INVALID_TOPIC_SIZE:
                        // the topic was too long
                        break;
                    case Response::RESULT_INVALID_PAYLOAD_SIZE:
                        // the payload was too large
                        break;
                    case Response::RESULT_INVALID_TOKEN:
                        // the token was invalid; remove it from your system
                        break;
                    case Response::RESULT_UNKNOWN_ERROR:
                        // apple didn't tell us what happened
                        break;
                }
            }
        } catch (RuntimeException $e) {
            //
        }
    }

    public function disconnect()
    {
        $this->client->close();
    }
}
