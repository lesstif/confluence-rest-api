<?php

namespace Lesstif\Confluence\Configuration;

/**
 * Interface ConfigurationInterface.
 */
interface ConfigurationInterface
{
    /**
     * Confluence host.
     *
     * @return string
     */
    public function getHost();

    /**
     * Confluence login user.
     *
     * @return string
     */
    public function getUser();

    /**
     * Confluence password.
     *
     * @return string
     */
    public function getPassword();

    /**
     * Path to log file.
     *
     * @return string
     */
    public function getLogFile();

    /**
     * Log level (DEBUG, INFO, ERROR, WARNING).
     *
     * @return string
     */
    public function getLogLevel();

    /**
     * Curl options CURLOPT_SSL_VERIFYHOST.
     *
     * @return bool
     */
    public function isSslVerify();

    /**
     * connection timeout
     *
     * @return integer
     */
    public function isTimeout();

    /**
     *
     * @return bool
     */
    public function isVerbose();
}
