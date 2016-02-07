<?php

namespace HipsterJazzbo\Telegraph;

class Message
{
    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $body;

    /**
     * @var array
     */
    private $data;

    /**
     * Message constructor.
     *
     * @param string $body
     * @param string $title
     * @param array  $data
     */
    public function __construct($body, $title = '', array $data = [])
    {
        $this->title = $title;
        $this->body  = $body;
        $this->data  = $data;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->data;
    }
}
