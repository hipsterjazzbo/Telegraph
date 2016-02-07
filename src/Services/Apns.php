<?php

namespace HipsterJazzbo\Telegraph\Services;

use HipsterJazzbo\Telegraph\Exceptions\ServiceException;
use HipsterJazzbo\Telegraph\Message;
use HipsterJazzbo\Telegraph\Pushable;
use Ramsey\Uuid\Uuid;
use ZendService\Apple\Apns\Client\Message as Client;
use ZendService\Apple\Apns\Message as ApnsMessage;
use ZendService\Apple\Apns\Message\Alert;
use ZendService\Apple\Apns\Response\Message as Response;

class Apns extends AbstractService
{
    /**
     * @var \ZendService\Apple\Apns\Client\Message
     */
    private $client;

    /**
     * Close the connection to APNS
     */
    public function disconnect()
    {
        $this->client->close();
    }

    /**
     * Return the service name
     *
     * @return string
     */
    protected function getServiceName()
    {
        return 'apns';
    }

    /**
     * Build a \ZendService\Apple\Apns\Message from a \HipsterJazzbo\Telegraph\Message
     *
     * @param \HipsterJazzbo\Telegraph\Pushable $pushable
     * @param \HipsterJazzbo\Telegraph\Message  $message
     *
     * @return \ZendService\Apple\Apns\Message
     */
    protected function buildServiceMessage(Pushable $pushable, Message $message)
    {
        $alert = new Alert();
        $alert->setTitle($message->getTitle());
        $alert->setBody($message->getBody());

        $apnsMessage = new ApnsMessage();
        $apnsMessage->setId((string)Uuid::uuid4());
        $apnsMessage->setToken($pushable->getToken());
        $apnsMessage->setAlert($alert);
        $apnsMessage->setCustom($message->getData());

        return $apnsMessage;
    }

    /**
     * Send the \ZendService\Apple\Apns\Message
     *
     * @param $serviceMessage
     *
     * @return \ZendService\Apple\Apns\Response\Message
     */
    protected function send($serviceMessage)
    {
        // So that we can close it later
        $this->client = new Client;

        $environment = ! array_get($this->config, 'sandbox', true) ? 1 : 0;

        $certificate = is_callable(array_get($this->config, 'certificate'))
            ? call_user_func(array_get($this->config, 'certificate'))
            : array_get($this->config, 'certificate');

        $this->client->open($environment, $certificate, array_get($this->config, 'passphrase'));

        return $this->client->send($serviceMessage);
    }

    /**
     * Handle the APNS response. Remove or retry if appropriate.
     *
     * @param Pushable $pushable
     * @param Message  $message
     * @param Response $response
     */
    protected function handleResponse(Pushable $pushable, Message $message, $response)
    {
        $error = false;

        if ($response->getCode() != Response::RESULT_OK) {
            switch ($response->getCode()) {
                case Response::RESULT_PROCESSING_ERROR:
                    $this->retry($pushable, $message);
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
        }

        if ($error !== false) {
            throw new ServiceException('apns', $error);
        }
    }
}
