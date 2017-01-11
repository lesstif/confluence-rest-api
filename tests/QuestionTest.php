<?php

use \Lesstif\Confluence\Question\QuestionService;

class QuestionTest extends PHPUnit_Framework_TestCase
{
    public function testGetQuestion()
    {
        $questionId = null;

        //$this->markTestSkipped();

        $queryParam = [
            // the number of questions needed (10 by default)
            'limit' => 10,

            //the start index (0 by default)
            'start' => 0,

            // The optional filter string which value is one of "unanswered", "popular", "my", "recent"
            // (default value 'recent')
            'filter' => 'popular',
        ];

        try {
            $qs = new QuestionService();

            $questions = $qs->getQuestion($queryParam);

            foreach($questions as $q) {
                echo sprintf("<a href=\"%s\">%s</a><p/>\n", $q->url, $q->title);
                //dump($q);

                $questionId = $q->id;
            }

        } catch (\Lesstif\Confluence\ConfluenceException $e) {
            $this->assertTrue(false, 'testSearch Failed : '.$e->getMessage());
        }

        return $questionId;
    }

    /**
     * @depends testGetQuestion
     */
    public function testGetQuestionDetail($questionId)
    {
        global $argv, $argc;

        // override command line parameter
        if ($argc > 2) {
            $questionId = $argv[2];
        }

        try {
            $qs = new QuestionService();

            $q = $qs->getQuestionDetail($questionId);

            foreach($q->answers as $a)
            {
                dump($a);
            }

        } catch (\Lesstif\Confluence\ConfluenceException $e) {
            $this->assertTrue(false, 'testSearch Failed : '.$e->getMessage());
        }

        return $questionId;
    }

    /**
     * @depends testGetQuestionDetail
     */
    public function testGetAcceptedAnswer($questionId)
    {
        global $argv, $argc;

        // override command line parameter
        if ($argc > 2) {
            $questionId = $argv[2];
        }

        try {
            $qs = new QuestionService();

            $ans = $qs->getAcceptedAnswer($questionId);

            dump(['Acccepted' => $ans]);
        } catch (\Lesstif\Confluence\ConfluenceException $e) {
            $this->assertTrue(false, 'testSearch Failed : '.$e->getMessage());
        }
    }
}
