<?php namespace Lesstif\Confluence\Answer;

use Lesstif\Confluence\ConfluenceClient;
use Lesstif\Confluence\ConfluenceException;
use Lesstif\Confluence\Constants;
use Lesstif\Confluence\Question\Question;
use Lesstif\Confluence\Question\QuestionService;

/**
 * Confluence Questions REST Service class
 *
 * @package Lesstif\Confluence\Answer
 * @see https://docs.atlassian.com/confluence-questions/rest/resource_AnswerResource.html
 */
class AnswerService extends ConfluenceClient
{
    // override parent uri
    public $url = '/questions/' . Constants::QUESTION_REST_API_VERSION . '/answer';

    private $defaultParam = [
                    'limit' => 10,
                    'start' => 0,
                    ];

    /**
     * get answer list
     *
     * @param string $username the user who made the answers
     * @param array|null $paramArray
     * @return mixed
     * @throws \Lesstif\Confluence\ConfluenceException
     */
    public function getAnswers($username, $paramArray = null)
    {
        if (empty($username))
        {
            throw new ConfluenceException('username must be set.! ');
        }

        // set default param
        if (empty($paramArray))
        {
            $paramArray = $this->defaultParam;
        }
        $paramArray['username'] = $username;

        $queryParam = '?' . http_build_query($paramArray);

        $ret = $this->exec($this->url . $queryParam, null);

        return $searchResults = $this->json_mapper->mapArray(
            json_decode($ret),  new \ArrayObject(), '\Lesstif\Confluence\Answer\Answer'
        );
    }

    /**
     * Get a answer detail by its ID
     *
     * @param string $answerId answer id
     *
     * @return Answer|null
     *
     * @throws ConfluenceException
     */
    public function getAnswerDetail(string $answerId) : ?Answer
    {
        if (empty($answerId))
        {
            throw new ConfluenceException('Answer id must be not null.! ');
        }

        $ret = $this->exec($this->url . '/' . $answerId, null);

        return $answer = $this->json_mapper->map(
            json_decode($ret),  new Answer()
        );
    }

    /**
     * getting related answer
     *
     * @param string $answerId
     *
     * @return Question|null
     * @throws ConfluenceException
     * @throws \JsonMapper_Exception
     */
    public function getQuestion(string $answerId) : ?Question
    {
        $answer = $this->getAnswerDetail($answerId);

        $qs = new QuestionService();

        return $qs->getQuestionDetail($answer->questionId);
    }
}