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
     * @var string
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
     * @param $log_level
     *
     * @return int
     */
    private function convertLogLevel($log_level)
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
     *  Execute REST get action.
     *
     * @param $uri
     *
     * @return mixed
     *
     * @throws
     */
    public function get($uri)
    {
        $client = new \GuzzleHttp\Client([
            'base_uri' => $this->gitHost,
            'timeout' => 10.0,
            'verify' => false,
        ]);
        $response = $client->get($this->gitHost.$this->api_uri.$uri, [
            'query' => [
                'private_token' => $this->gitToken,
                'per_page' => 10000,
            ],
        ]);
        if ($response->getStatusCode() != 200) {
            throw GitlabException('Http request failed. status code : '
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

        $this->log->addDebug("Curl $url JsonData=".$post_data);

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

        $this->log->addDebug('Curl exec='. $url . ',customreq=' . $custom_request);
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
            $this->log->addError('CURL Error: = '.$body);
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

            $this->log->addDebug('using legacy file upload');
        } else {
            // CURLFile require PHP > 5.5
            $attachments = new \CURLFile(realpath($upload_file));
            $attachments->setPostFilename(basename($upload_file));

            curl_setopt($ch, CURLOPT_POSTFIELDS,
                    array('file' => $attachments));

            $this->log->addDebug('using CURLFile='.var_export($attachments, true));
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

        $this->log->addDebug('Curl exec='.$url);

        return $ch;
    }

    /**
     * File upload.
     *
     * @param string $context       url context
     * @param array  $filePathArray upload file path.
     *
     * @return array
     *
     * @throws ConfluenceException
     */
    public function upload($context, $filePathArray)
    {
        $url = $this->createUrlByContext($context);

        // return value
        $result_code = 200;

        $chArr = array();
        $results = array();
        $mh = curl_multi_init();

        for ($idx = 0; $idx < count($filePathArray); ++$idx) {
            $file = $filePathArray[$idx];
            if (file_exists($file) == false) {
                $body = "File $file not found";
                $result_code = -1;
                goto end;
            }
            $chArr[$idx] = $this->createUploadHandle($url, $filePathArray[$idx]);

            curl_multi_add_handle($mh, $chArr[$idx]);
        }

        $running = null;
        do {
            curl_multi_exec($mh, $running);
        } while ($running > 0);

         // Get content and remove handles.
        for ($idx = 0; $idx < count($chArr); ++$idx) {
            $ch = $chArr[$idx];

            $results[$idx] = curl_multi_getcontent($ch);

            // if request failed.
            if (!$results[$idx]) {
                $this->http_response = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                $body = curl_error($ch);

                //The server successfully processed the request, but is not returning any content.
                if ($this->http_response == 204) {
                    continue;
                }

                // HostNotFound, No route to Host, etc Network error
                $result_code = -1;
                $body = 'CURL Error: = '.$body;
                $this->log->addError($body);
            } else {
                // if request was ok, parsing http response code.
                $result_code = $this->http_response = curl_getinfo($ch, CURLINFO_HTTP_CODE);

                // don't check 301, 302 because setting CURLOPT_FOLLOWLOCATION
                if ($this->http_response != 200 && $this->http_response != 201) {
                    $body = 'CURL HTTP Request Failed: Status Code : '
                     .$this->http_response.', URL:'.$url
                     ."\nError Message : ".$response; // @TODO undefined variable $response
                    $this->log->addError($body);
                }
            }
        }

        // clean up
end:
        foreach ($chArr as $ch) {
            $this->log->addDebug('CURL Close handle..');
            curl_close($ch);
            curl_multi_remove_handle($mh, $ch);
        }
        $this->log->addDebug('CURL Multi Close handle..');
        curl_multi_close($mh);
        if ($result_code != 200) {
            // @TODO $body might have not been defined
            throw new ConfluenceException('CURL Error: = '.$body, $result_code);
        }

        return $results;
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
}
