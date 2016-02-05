<?php

namespace HipsterJazzbo\Telegraph\Services;

use HipsterJazzbo\Telegraph\Exceptions\ServiceException;
use HipsterJazzbo\Telegraph\Message;
use HipsterJazzbo\Telegraph\Push;
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

    /**
     * @var callable
     */
    private $removeCallback;

    /**
     * @var callable
     */
    private $updateCallback;

    public function __construct(Push $push)
    {
        $config = $push->getConfig('gcm');

        $this->client = new Client();
        $this->client->setApiKey(array_get($config, 'key'));

        $this->removeCallback = $push->getRemoveCallback();
        $this->updateCallback = $push->getUpdateCallback();
    }

    public function push(Pushable $pushable, Message $message, callable $failure = null)
    {
        $gcmMessage = new GcmMessage();
        $gcmMessage->addRegistrationId($pushable->getToken());
        $gcmMessage->setTitle($message->getTitle());
        $gcmMessage->setBody($message->getBody());
        $gcmMessage->setData($message->getData());

        try {
            $response = $this->client->send($gcmMessage);
            $results  = $response->getResults();
            $result   = $results[$pushable->getToken()];

            if (isset($result['message_id']) && isset($result['registration_id'])) {
                call_user_func($this->updateCallback, $pushable, $result['registration_id']);
            } elseif (isset($result['error'])) {
                switch ($result['error']) {
                    case 'Unavailable':
                        // retry
                        break;

                    case 'NotRegistered':
                        call_user_func($this->removeCallback, $pushable);
                        break;

                    default:
                        throw new ServiceException('gcm', $result['error']);
                }
            }
        } catch (RuntimeException $e) {
            //
        }
    }

    public function disconnect()
    {
        //
    }
}
