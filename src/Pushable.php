<?php

namespace HipsterJazzbo\Telegraph;

interface Pushable
{
    /**
     * @return string The device token
     */
    public function getToken();

    /**
     * @return string A valid service. "apns" or "gcm"
     */
    public function getService();
}
