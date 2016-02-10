<?php

namespace HipsterJazzbo\Telegraph;

class Message
{
    /**
     * @var string
     */
    private $body;

    /**
     * @var string
     */
    private $title;

    /**
     * @var array
     */
    private $data;

    /**
     * @var int|null
     */
    private $badge;

    /**
     * Message constructor.
     *
     * @param string   $body
     * @param string   $title
     * @param array    $data
     * @param int|null $badge
     */
    public function __construct($body = '', $title = '', array $data = [], $badge = null)
    {
        $this->title = $title;
        $this->body  = $body;
        $this->data  = $data;
        $this->badge = $badge;
    }

    /**
     * @param string $body
     *
     * @return Message
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * @param string $title
     *
     * @return Message
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * @param array $data
     *
     * @return Message
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @param int|null $badge
     *
     * @return Message
     */
    public function setBadge($badge)
    {
        $this->badge = $badge;

        return $this;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @return int|null
     */
    public function getBadge()
    {
        return $this->badge;
    }
}
