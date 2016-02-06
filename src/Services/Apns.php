<?php

namespace HipsterJazzbo\Telegraph\Services;

use HipsterJazzbo\Telegraph\Exceptions\ServiceException;
use HipsterJazzbo\Telegraph\Message;
use HipsterJazzbo\Telegraph\Push;
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

    /**
     * @var callable|null
     */
    private $removeCallback;

    public function __construct(Push $push)
    {
        $this->client         = new Client;
        $this->removeCallback = $push->getRemoveCallback();

        $config = $push->getConfig('apns');

        $environment = ! array_get($config, 'sandbox', true) ? 1 : 0;

        $certificate = is_callable(array_get($config, 'certificate'))
            ? call_user_func(array_get($config, 'certificate'))
            : array_get($config, 'certificate');

        $this->client->open($environment, $certificate, array_get($config, 'passphrase'));
    }

    public function push(Pushable $pushable, Message $message)
    {
        $alert = new Alert();
        $alert->setTitle($message->getTitle());
        $alert->setBody($message->getBody());

        $apnsMessage = new ApnsMessage();
        $apnsMessage->setId((string) Uuid::uuid4());
        $apnsMessage->setToken($pushable->getToken());
        $apnsMessage->setAlert($alert);
        $apnsMessage->setCustom($message->getData());

        try {
            $response = $this->client->send($apnsMessage);
            $error    = false;

            if ($response->getCode() != Response::RESULT_OK) {
                switch ($response->getCode()) {
                    case Response::RESULT_PROCESSING_ERROR:
                        // you may want to retry
                        break;

                    case Response::RESULT_INVALID_TOKEN:
                        if (is_callable($this->removeCallback)) {
                            call_user_func($this->removeCallback, $pushable);
                        }
                        break;

                    case Response::RESULT_MISSING_TOKEN:
                        $error = "You were missing a token";
                        break;

                    case Response::RESULT_MISSING_TOPIC:
                        $error = "You are missing a message id";
                        break;

                    case Response::RESULT_MISSING_PAYLOAD:
                        $error = "You need to send a payload";
                        break;

                    case Response::RESULT_INVALID_TOKEN_SIZE:
                        $error = "The token provided was not of the proper size";
                        break;

                    case Response::RESULT_INVALID_TOPIC_SIZE:
                        $error = "The topic was too long";
                        break;

                    case Response::RESULT_INVALID_PAYLOAD_SIZE:
                        $error = "The payload was too large";
                        break;

                    case Response::RESULT_UNKNOWN_ERROR:
                        $error = "Apple didn't tell us what happened";
                        break;

                    default:
                        $error = "Unknown error";
                        break;
                }

                if ($error) {
                    throw new ServiceException('apns', $error);
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
