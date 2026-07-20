<?php

/**
 * create default session of 15 minutes, remove it on log out and renew it on login
 */

/**
 * Session cookie manager.
 */
class THMVSession {

    public static function set($name, $value, $expirationTime = false) {
        //set option cookie with current time.
        $date = new DateTime();
        if (!$expirationTime) {
            $expirationTime = '+15 minutes'; // default session cookie time
        }
        $date->modify($expirationTime);
        $finalExpirationTime = $date->getTimestamp();
        update_option('thmv_' . $name, $value);
        update_option('thmv_' . $name . '_time', $finalExpirationTime);
    }

    /**
     * Return a cookie
     * @return mixed
     */
    public static function get($name) {
        //get the time of the cookie, and if it's expired, remove it first
        $expirationTime = get_option('thmv_' . $name . '_time', null);
        $value = get_option('thmv_' . $name, null);
        if ($value === null) {
            return null;
        }

        if (!$expirationTime) {
            self::delete($name);
            return null;
        }

        $date = new DateTime();
        $nowTime = $date->getTimestamp();
        if ($expirationTime < $nowTime) {
            self::delete($name);
            return null;
        }

        return $value;
    }

    /**
     * Delete cookie.
     * @return bool
     */
    public static function delete($name) {
        delete_option('thmv_' . $name);
        delete_option('thmv_' . $name . '_time');
    }

}
