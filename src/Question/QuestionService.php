<?php namespace Lesstif\Confluence\Question;

use Lesstif\Confluence\Answer\Answer;
use Lesstif\Confluence\Answer\AnswerService;
use Lesstif\Confluence\ConfluenceClient;
use Lesstif\Confluence\ConfluenceException;

use Lesstif\Confluence\Constants;

/**
 * Confluence Questions REST Service class
 *
 * @package Lesstif\Confluence\Question
 *
 */
class QuestionService extends ConfluenceClient
{
    // override parent uri
    public $url = '/questions/' . Constants::QUESTION_REST_API_VERSION . '/';

    private $defaultParam = [
                    'limit' => 10,
                    'start' => 0,
                    'filter' => 'recent',
                    ];

   private $accceptedAnswerId = null;

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
     * @param string $questionId question id
     *
     * @return Question|null
     */
    public function getQuestionDetail(string $questionId)
    {
        // clear old value
        $this->accceptedAnswerId = null;

        if (empty($questionId))
        {
            throw new ConfluenceException('Question id must be not null.! ');
        }

        $ret = $this->exec($this->url . 'question/' . $questionId, null);

        $question = $this->json_mapper->map(
            json_decode($ret),  new Question()
        );

        $this->accceptedAnswerId = $question->accceptedAnswerId;

        return $question;
    }

    /**
     * Get a accepted answer
     *
     * @param string $questionId
     * @return Answer|null
     * @throws ConfluenceException
     */
    public function getAcceptedAnswer(string $questionId) : ?Answer
    {
        $question = $this->getQuestionDetail($questionId);

        if (empty($question) || empty($question->acceptedAnswerId))
        {
            return null;
        }

        $as = new AnswerService();

        return $as->getAnswerDetail($question->acceptedAnswerId);
    }

    /**
     * determine question has accepted answer
     *
     * @param string|null $questionId
     * @return bool
     */
    public function hasAcceptedAnswer(string $questionId = null) : bool
    {
        if ($questionId === null) {
            return !is_null($this->accceptedAnswerId) ? true : false;
        }

        $as = $this->getAcceptedAnswer($questionId);

        return !is_null($as) ? true : false;
    }
}