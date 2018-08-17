<?php

/**
 * @package core
 */

 /**
  * The Session class is a handler for all Session related logic in PHP. The functions
  * map directly to all handler functions as defined by session_set_save_handler in
  * PHP. In Symphony, this function is used in conjunction with the `Cookie` class.
  * Based on: http://php.net/manual/en/function.session-set-save-handler.php#81761
  * by klose at openriverbed dot de which was based on
  * http://php.net/manual/en/function.session-set-save-handler.php#79706 by
  * maria at junkies dot jp
  *
  * @link http://php.net/manual/en/function.session-set-save-handler.php
  */

class Session
{
    /**
     * If a Session has been created, this will be true, otherwise false
     *
     * @var boolean
     */
    private static $_initialized = false;

    /**
     * Starts a Session object, only if one doesn't already exist. This function maps
     * the Session Handler functions to this classes methods by reading the default
     * information from the PHP ini file.
     *
     * @link http://php.net/manual/en/function.session-set-save-handler.php
     * @link http://php.net/manual/en/function.session-set-cookie-params.php
     * @param integer $lifetime
     *  How long a Session is valid for, by default this is 0, which means it
     *  never expires
     * @param string $path
     *  The path the cookie is valid for on the domain
     * @param string $domain
     *  The domain this cookie is valid for
     * @param boolean $httpOnly
     *  Whether this cookie can be read by Javascript. By default the cookie
     *  cannot be read by Javascript
     * @param boolean $secure
     *  Whether this cookie should only be sent on secure servers. By default this is
     *  false, which means the cookie can be sent over HTTP and HTTPS
     * @throws Exception
     * @return string|boolean
     *  Returns the Session ID on success, or false on error.
     */
    public static function start($lifetime = 0, $path = '/', $domain = null, $httpOnly = true, $secure = false)
    {
        if (!self::$_initialized) {
            if (!is_object(Symphony::Database()) || !Symphony::Database()->isConnected()) {
                return false;
            }

            if (session_id() == '') {
                ini_set('session.use_trans_sid', '0');
                ini_set('session.use_strict_mode', '1');
                ini_set('session.use_only_cookies', '1');
                ini_set('session.gc_maxlifetime', $lifetime);
                ini_set('session.gc_probability', '1');
                ini_set('session.gc_divisor', Symphony::Configuration()->get('session_gc_divisor', 'symphony'));
            }

            session_set_save_handler(
                array('Session', 'open'),
                array('Session', 'close'),
                array('Session', 'read'),
                array('Session', 'write'),
                array('Session', 'destroy'),
                array('Session', 'gc')
            );

            session_set_cookie_params(
                $lifetime,
                static::createCookieSafePath($path),
                ($domain ? $domain : self::getDomain()),
                $secure,
                $httpOnly
            );
            session_cache_limiter('');

            if (session_id() == '') {
                if (headers_sent()) {
                    throw new Exception('Headers already sent. Cannot start session.');
                }

                register_shutdown_function('session_write_close');
                session_start();
            }

            self::$_initialized = true;
        }

        return session_id();
    }

    /**
     * Returns a properly formatted ascii string for the cookie path.
     * Browsers are notoriously bad at parsing the cookie path. They do not
     * respect the content-encoding header. So we must be careful when dealing
     * with setups with special characters in their paths.
     *
     * @since Symphony 2.7.0
     **/
    protected static function createCookieSafePath($path)
    {
        $path = array_filter(explode('/', $path));
        if (empty($path)) {
            return '/';
        }
        $path = array_map('rawurlencode', $path);
        return '/' . implode('/', $path);
    }

    /**
     * Returns the current domain for the Session to be saved to, if the installation
     * is on localhost, this returns null and just allows PHP to take care of setting
     * the valid domain for the Session, otherwise it will return the non-www version
     * of the domain host.
     *
     * @return string|null
     *  Null if on localhost, or HTTP_HOST is not set, a string of the domain name sans
     *  www otherwise
     */
    public static function getDomain()
    {
        if (HTTP_HOST != null) {
            if (preg_match('/(localhost|127\.0\.0\.1)/', HTTP_HOST)) {
                return null; // prevent problems on local setups
            }

            return preg_replace('/(^www\.|:\d+$)/i', null, HTTP_HOST);
        }

        return null;
    }

    /**
     * Allows the Session to open without any further logic.
     *
     * @return boolean
     *  Always returns true
     */
    public static function open()
    {
        return true;
    }

    /**
     * Allows the Session to close without any further logic. Acts as a
     * destructor function for the Session.
     *
     * @return boolean
     *  Always returns true
     */
    public static function close()
    {
        return true;
    }

    /**
     * Given an ID, and some data, save it into `tbl_sessions`. This uses
     * the ID as a unique key, and will override any existing data. If the
     * `$data` is deemed to be empty, no row will be saved in the database
     * unless there is an existing row.
     *
     * @param string $id
     *  The ID of the Session, usually a hash
     * @param string $data
     *  The Session information, usually a serialized object of
     * `$_SESSION[Cookie->_index]`
     * @throws DatabaseException
     * @return boolean
     *  true if the Session information was saved successfully, false otherwise
     */
    public static function write($id, $data)
    {
        // Only prevent this record from saving if there isn't already a record
        // in the database. This prevents empty Sessions from being created, but
        // allows them to be nulled.
        $session_data = Session::read($id);
        if (!$session_data) {
            $empty = true;
            if (session_status() === PHP_SESSION_ACTIVE) {
                $unserialized_data = Session::unserialize($data);

                foreach ($unserialized_data as $d) {
                    if (!empty($d)) {
                        $empty = false;
                    }
                }

                if ($empty) {
                    return true;
                }
            // PHP 7.0 makes the session inactive in write callback,
            // so we try to detect empty sessions without decoding them
            } elseif ($data === Symphony::Configuration()->get('cookie_prefix', 'symphony') . '|a:0:{}') {
                return true;
            }
        }

        $fields = array(
            'session' => $id,
            'session_expires' => time(),
            'session_data' => $data
        );

        return Symphony::Database()->insert($fields, 'tbl_sessions', true);
    }

    /**
     * Given raw session data return the unserialized array.
     * Used to check if the session is really empty before writing.
     *
     * @since Symphony 2.3.3
     * @param string $data
     *  The serialized session data
     * @return array
     *  The unserialised session data
     */
    private static function unserialize($data)
    {
        $hasBuffer = isset($_SESSION);
        $buffer = $_SESSION;
        session_decode($data);
        $session = $_SESSION;

        if ($hasBuffer) {
            $_SESSION = $buffer;
        } else {
            unset($_SESSION);
        }

        return $session;
    }

    /**
     * Given a session's ID, return it's row from `tbl_sessions`
     *
     * @param string $id
     *  The identifier for the Session to fetch
     * @return string
     *  The serialised session data
     */
    public static function read($id)
    {
        return (string)Symphony::Database()->fetchVar(
            'session_data',
            0,
            sprintf(
                "SELECT `session_data`
                FROM `tbl_sessions`
                WHERE `session` = '%s'
                LIMIT 1",
                Symphony::Database()->cleanValue($id)
            )
        );
    }

    /**
     * Given a session's ID, remove it's row from `tbl_sessions`
     *
     * @param string $id
     *  The identifier for the Session to destroy
     * @throws DatabaseException
     * @return boolean
     *  true if the Session was deleted successfully, false otherwise
     */
    public static function destroy($id)
    {
        return Symphony::Database()->query(
            sprintf(
                "DELETE
                FROM `tbl_sessions`
                WHERE `session` = '%s'",
                Symphony::Database()->cleanValue($id)
            )
        );
    }

    /**
     * The garbage collector, which removes all empty Sessions, or any
     * Sessions that have expired. This has a 10% chance of firing based
     * off the `gc_probability`/`gc_divisor`.
     *
     * @param integer $max
     *  The max session lifetime.
     * @throws DatabaseException
     * @return boolean
     *  true on Session deletion, false if an error occurs
     */
    public static function gc($max)
    {
        return Symphony::Database()->query(
            sprintf(
                "DELETE
                FROM `tbl_sessions`
                WHERE `session_expires` <= %d",
                Symphony::Database()->cleanValue(time() - $max)
            )
        );
    }
}
