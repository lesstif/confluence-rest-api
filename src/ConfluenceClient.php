<?php

namespace Lesstif\Confluence;

use Lesstif\Confluence\Configuration\ConfigurationInterface;
use Lesstif\Confluence\Configuration\DotEnvConfiguration;
use Monolog\Logger as Logger;
use Monolog\Handler\StreamHandler;

/**
 * Interact confluence server with REST API.
 */
class ConfluenceClient
{
    /**
     * Json Mapper.
     *
     * @var \JsonMapper
     */
    protected $json_mapper;

    /**
     * HTTP response code.
     *
     * @var int
     */
    protected $http_response;

    /**
     * Confluence REST API URI.
     *
     * @var string
     */
    protected $api_uri = '/rest';

    /**
     * guzzle instance.
     *
     * @var resource
     */
    protected $curl;

    /**
     * Monolog instance.
     *
     * @var \Monolog\Logger
     */
    protected $log;

    /**
     * Confluence Rest API Configuration.
     *
     * @var ConfigurationInterface
     */
    protected $configuration;

    /**
     * Constructor.
     *
     * @param ConfigurationInterface $configuration
     * @param Logger                 $logger
     */
    public function __construct(ConfigurationInterface $configuration = null, Logger $logger = null)
    {
        if ($configuration === null) {
            $path = './';
            if (!file_exists('.env')) {
                // If calling the getcwd() on laravel it will returning the 'public' directory.
                $path = '../';
            }
            $configuration = new DotEnvConfiguration($path);
        }

        $this->configuration = $configuration;
        $this->json_mapper = new \JsonMapper();

        $this->json_mapper->undefinedPropertyHandler = [
            \Lesstif\Confluence\JsonMapperHelper::class,
            'setUndefinedProperty',
        ];

        // create logger
        if ($logger) {
            $this->log = $logger;
        } else {
            $this->log = new Logger('Confluence');
            $this->log->pushHandler(new StreamHandler(
                $configuration->getLogFile(),
                $this->convertLogLevel($configuration->getLogLevel())
            ));
        }

        $this->http_response = 200;
    }

    /**
     * Convert log level.
     *
     * @param string $log_level
     *
     * @return int
     */
    private function convertLogLevel(string $log_level) : int
    {
        switch ($log_level) {
            case 'DEBUG':
                return Logger::DEBUG;
            case 'INFO':
                return Logger::INFO;
            case 'ERROR':
                return Logger::ERROR;
            default:
                return Logger::WARNING;
        }
    }

    /**
     * Serilize only not null field.
     *
     * @param array $haystack
     *
     * @return array
     */
    protected function filterNullVariable($haystack)
    {
        foreach ($haystack as $key => $value) {
            if (is_array($value)) {
                $haystack[$key] = $this->filterNullVariable($haystack[$key]);
            } elseif (is_object($value)) {
                $haystack[$key] = $this->filterNullVariable(get_class_vars(get_class($value)));
            }

            if (is_null($haystack[$key]) || empty($haystack[$key])) {
                unset($haystack[$key]);
            }
        }

        return $haystack;
    }

    /**
     *
     * Execute REST get action.
     *
     * @param string $uri URI
     * @param array $httpParam
     * @return mixed
     * @throws ConfluenceException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function get(string $uri, array $httpParam = [])
    {
        $client = $this->newGuzzleClient();

        $response = $client->get($this->api_uri.$uri, [
            'query' => [
                'per_page' => 10,
            ],
        ]);

        if ($response->getStatusCode() != 200) {
            throw new ConfluenceException('Http request failed. status code : '
                .$response->getStatusCode().' reason:'.$response->getReasonPhrase());
        }

        return json_decode($response->getBody());
    }

    /**
     * Execute REST request.
     *
     * @param string $context        Rest API context (ex.:issue, search, etc..)
     * @param string $post_data
     * @param string $custom_request [PUT|DELETE]
     *
     * @return string
     *
     * @throws ConfluenceException
     */
    public function exec($context, $post_data = null, $custom_request = null, $isFqdn = false)
    {
        $url = $this->createUrlByContext($context, $isFqdn);

        $this->log->debug("Curl $url JsonData=".$post_data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);

        // post_data
        if (!is_null($post_data)) {
            // PUT REQUEST
            if (!is_null($custom_request) && $custom_request == 'PUT') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            }
            if (!is_null($custom_request) && $custom_request == 'DELETE') {
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'DELETE');
            } else {
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
            }
        }

        $this->authorization($ch);

        if (!$this->getConfiguration()->isSslVerify()) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        }

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array('Accept: */*', 'Content-Type: application/json'));

        curl_setopt($ch, CURLOPT_VERBOSE, $this->getConfiguration()->isVerbose());

        $this->log->debug('Curl exec='. $url . ',customreq=' . $custom_request);
        $response = curl_exec($ch);

        // if request failed.
        if (!$response) {
            $this->http_response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $body = curl_error($ch);
            curl_close($ch);

            //The server successfully processed the request, but is not returning any content.
            if ($this->http_response == 204) {
                return '';
            }

            // HostNotFound, No route to Host, etc Network error
            $this->log->error('CURL Error: = '.$body);
            throw new ConfluenceException('CURL Error: = '.$body);
        } else {
            // if request was ok, parsing http response code.
            $this->http_response = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            // don't check 301, 302 because setting CURLOPT_FOLLOWLOCATION
            if ($this->http_response != 200 && $this->http_response != 201) {
                throw new ConfluenceException('CURL HTTP Request Failed: Status Code : '
                 .$this->http_response.', URL:'.$url
                 ."\nError Message : ".$response, $this->http_response);
            }
        }

        return $response;
    }

    /**
     * Create upload handle.
     *
     * @param string $url         Request URL
     * @param string $upload_file Filename
     *
     * @return resource
     */
    private function createUploadHandle($url, $upload_file)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_URL, $url);

        // send file
        curl_setopt($ch, CURLOPT_POST, true);

        if (PHP_MAJOR_VERSION == 5 && PHP_MINOR_VERSION  < 5) {
            $attachments = realpath($upload_file);
            $filename = basename($upload_file);

            curl_setopt($ch, CURLOPT_POSTFIELDS,
                array('file' => '@'.$attachments.';filename='.$filename));

            $this->log->debug('using legacy file upload');
        } else {
            // CURLFile require PHP > 5.5
            $attachments = new \CURLFile(realpath($upload_file));
            $attachments->setPostFilename(basename($upload_file));

            curl_setopt($ch, CURLOPT_POSTFIELDS,
                    array('file' => $attachments));

            $this->log->debug('using CURLFile='.var_export($attachments, true));
        }

        $this->authorization($ch);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, $this->getConfiguration()->isCurlOptSslVerifyHost());
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $this->getConfiguration()->isCurlOptSslVerifyPeer());

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            array(
                'Accept: */*',
                'Content-Type: multipart/form-data',
                'X-Atlassian-Token: nocheck',
                ));

        curl_setopt($ch, CURLOPT_VERBOSE, $this->getConfiguration()->isCurlOptVerbose());

        $this->log->debug('Curl exec='.$url);

        return $ch;
    }

    /**
     * File upload.
     *
     * TODO impl
     *
     * @param array  $filePathArray upload file path.
     *
     * @return array
     *
     *
     * @throws ConfluenceException
     */
    public function upload(array $filePathArray)
    {

    }

    /**
     * Get URL by context.
     *
     * @param string $context
     *
     * @return string
     */
    protected function createUrlByContext($context, $isFqdn = false)
    {
        if ($isFqdn == true){
            return $context;
        }

        $host = $this->getConfiguration()->getHost();

        return $host.$this->api_uri.'/'.preg_replace('/\//', '', $context, 1);
    }

    /**
     * Add authorize to curl request.
     *
     * @TODO session/oauth methods
     *
     * @param resource $ch
     */
    protected function authorization($ch)
    {
        $username = $this->getConfiguration()->getUser();
        $password = $this->getConfiguration()->getPassword();
        if (!empty($username) && !empty($password)) {
            curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
        }
    }

    /**
     * Confluence Rest API Configuration.
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * @param array $param
     * @return \GuzzleHttp\Client
     */
    private function newGuzzleClient(array $param = [])
    {
        $param = array_merge([
            'base_uri' => $this->configuration->getHost(),
            'timeout' => 10.0,
            'verify' => false,
        ], $param);

        $guzzle = new \GuzzleHttp\Client($param);

        return $guzzle;
    }
}
