<?php namespace Lesstif\Confluence\Question;

use Lesstif\Confluence\ConfluenceClient;
use Lesstif\Confluence\ConfluenceException;

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

    /**
     * Get a question by its ID
     *
     * @param $id question id
     *
     * @return string
     */
    public function getQuestionDetail($id)
    {
        if (empty($id))
        {
            throw new ConfluenceException('Question id must be not null.! ');
        }

        $ret = $this->exec($this->url . 'question/' . $id, null);

        return $searchResults = $this->json_mapper->map(
            json_decode($ret),  new Question()
        );

    }
}