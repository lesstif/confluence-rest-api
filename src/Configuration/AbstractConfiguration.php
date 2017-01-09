<?php

namespace Lesstif\Confluence\Configuration;

/**
 * Class AbstractConfiguration.
 */
abstract class AbstractConfiguration implements ConfigurationInterface
{
    /**
     * Confluence host.
     *
     * @var string
     */
    protected $host;

    /**
     * Confluence user id.
     *
     * @var string
     */
    protected $user;

    /**
     * Confluence password.
     *
     * @var string
     */
    protected $password;

    /**
     * Path to log file.
     *
     * @var string
     */
    protected $logFile;

    /**
     * Log level (DEBUG, INFO, ERROR, WARNING).
     *
     * @var string
     */
    protected $logLevel;

    /**
     * verify SSL Host
     *
     * @var bool
     */
    protected $sslVerify;

    /**
     * connection timeout
     *
     * @var integer
     */
    protected $timeOut;

    /**
     *
     * @var bool
     */
    protected $verbose;

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getLogFile()
    {
        return $this->logFile;
    }

    /**
     * @return string
     */
    public function getLogLevel()
    {
        return $this->logLevel;
    }

    /**
     * @return bool
     */
    public function isTimeOut()
    {
        return $this->timeOut;
    }

    /**
     * @return bool
     */
    public function isSslVerify()
    {
        return $this->sslVerify;
    }

    /**
     * @return bool
     */
    public function isVerbose()
    {
        return $this->verbose;
    }
}
