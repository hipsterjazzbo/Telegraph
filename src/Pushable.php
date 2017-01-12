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

    /**
     * Update the device token.
     *
     * @param $newToken
     *
     * @return void
     */
    public function updateToken($newToken);

    /**
     * Remove the device if instructed by the push service.
     *
     * @return void
     */
    public function remove();
}
