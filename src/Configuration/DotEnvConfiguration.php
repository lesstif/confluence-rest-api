<?php namespace Lesstif\Confluence\Configuration;

/**
 * Class DotEnvConfiguration.
 */
class DotEnvConfiguration extends AbstractConfiguration
{
    /**
     * @param string $path
     */
    public function __construct($path = '.')
    {
        $dotenv = new \Dotenv\Dotenv($path);
        $dotenv->load();
        $dotenv->required(['CONFLUENCE_HOST']);

        $this->host = $this->env('CONFLUENCE_HOST');
        $this->userser = $this->env('CONFLUENCE_USER');
        $this->passwordassword = $this->env('CONFLUENCE_PASS');
        $this->logFile = $this->env('CONFLUENCE_LOG_FILE', 'confluence-rest-client.log');
        $this->logLevel = $this->env('CONFLUENCE_LOG_LEVEL', 'WARNING');
        $this->sslVerify = $this->env('SSL_VERIFY', false);
        $this->timeOut = $this->env('TIMEOUT', 10.0);
        $this->verbose = $this->env('VERBOSE', false);
    }

    /**
     * Gets the value of an environment variable. Supports boolean, empty and null.
     *
     * @param string $key
     * @param mixed  $default
     *
     * @return mixed
     */
    private function env($key, $default = null)
    {
        $value = getenv($key);

        if ($value === false) {
            return $default;
        }

        switch (strtolower($value)) {
            case 'true':
            case '(true)':
                return true;

            case 'false':
            case '(false)':
                return false;

            case 'empty':
            case '(empty)':
                return '';

            case 'null':
            case '(null)':
                return;
        }

        if ($this->startsWith($value, '"') && endsWith($value, '"')) {
            return substr($value, 1, -1);
        }

        return $value;
    }

    /**
     * Determine if a given string starts with a given substring.
     *
     * @param string       $haystack
     * @param string|array $needles
     *
     * @return bool
     */
    public function startsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ($needle != '' && strpos($haystack, $needle) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if a given string ends with a given substring.
     *
     * @param string       $haystack
     * @param string|array $needles
     *
     * @return bool
     */
    public function endsWith($haystack, $needles)
    {
        foreach ((array) $needles as $needle) {
            if ((string) $needle === substr($haystack, -strlen($needle))) {
                return true;
            }
        }

        return false;
    }
}
