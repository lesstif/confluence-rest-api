<?php namespace Lesstif\Confluence\Question;

use Lesstif\Confluence\ConfluenceClient;

/**
 * Confluence Questions REST Service class
 *
 * @package Lesstif\Confluence\Question
 */
class QuestionService extends ConfluenceClient
{
    const QUESTION_REST_VERSION = '1.0';

    // override parent uri
    public $url = '/questions/1.0/';

    private $defaultParam = [
                    'limit' => 10,
                    'start' => 0,
                    'filter' => 'recent',
                    ];

    /**
     * get question list
     *
     * @param null $paramArray
     * @return mixed
     * @throws \Lesstif\Confluence\ConfluenceException
     */
    public function getQuestion($paramArray = null)
    {
        // set default param
        if (empty($paramArray))
        {
            $paramArray = $this->defaultParam;
        }

        $queryParam = 'question?' . http_build_query($paramArray);

        $ret = $this->exec($this->url . $queryParam, null);

        return $searchResults = $this->json_mapper->mapArray(
            json_decode($ret),  new \ArrayObject(), '\Lesstif\Confluence\Question\Question'
        );
    }

}