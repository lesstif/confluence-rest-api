<?php

namespace Lesstif\Confluence\Configuration;

/**
 * Class ArrayConfiguration.
 */
class ArrayConfiguration extends AbstractConfiguration
{
    /**
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->logFile = 'conflience-rest-client.log';
        $this->logLevel = 'WARNING';
        $this->sslVerify = false;
        $this->timeOut = 10.0;
        $this->verbose = false;

        foreach ($configuration as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }
}
