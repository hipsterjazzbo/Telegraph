<?php

namespace HipsterJazzbo\Telegraph\Services;

use HipsterJazzbo\Telegraph\Exceptions\ServiceException;
use HipsterJazzbo\Telegraph\Message;
use HipsterJazzbo\Telegraph\Pushable;
use ZendService\Google\Gcm\Client;
use ZendService\Google\Gcm\Message as GcmMessage;

class Gcm extends AbstractService
{
    /**
     * Doesn't need to do anything for GCM
     */
    public function disconnect()
    {
        //
    }

    /**
     * Return the service name
     *
     * @return string
     */
    protected function getServiceName()
    {
        return 'gcm';
    }

    /**
     * Build a \ZendService\Google\Gcm\Message from a \HipsterJazzbo\Telegraph\Message
     *
     * @param \HipsterJazzbo\Telegraph\Pushable $pushable
     * @param \HipsterJazzbo\Telegraph\Message  $message
     *
     * @return \ZendService\Google\Gcm\Message
     */
    protected function buildServiceMessage(Pushable $pushable, Message $message)
    {
        $gcmMessage = new GcmMessage();
        $gcmMessage->addRegistrationId($pushable->getToken());
        $gcmMessage->setTitle($message->getTitle());
        $gcmMessage->setBody($message->getBody());
        $gcmMessage->setData($message->getData());

        return $gcmMessage;
    }

    /**
     * Send the \ZendService\Google\Gcm\Message
     *
     * @param $serviceMessage
     *
     * @return \ZendService\Google\Gcm\Response
     */
    protected function send($serviceMessage)
    {
        $client = new Client();
        $client->setApiKey(array_get($this->config, 'key'));

        return $client->send($serviceMessage);
    }

    /**
     * Handle the GCM response. Remove, update or retry if appropriate.
     *
     * @param \HipsterJazzbo\Telegraph\Pushable $pushable
     * @param \HipsterJazzbo\Telegraph\Message  $message
     * @param \ZendService\Google\Gcm\Response  $response
     */
    protected function handleResponse(Pushable $pushable, Message $message, $response)
    {
        $results = $response->getResults();
        $result  = $results[$pushable->getToken()];

        if (isset($result['message_id']) && isset($result['registration_id'])) {
            $pushable->updateToken($result['registration_id']);
        } elseif (isset($result['error'])) {
            switch ($result['error']) {
                case 'Unavailable':
                    $this->retry($pushable, $message);
                    break;

                case 'NotRegistered':
                    $pushable->remove();
                    break;

                default:
                    throw new ServiceException('gcm', $result['error']);
            }
        }
    }
}
