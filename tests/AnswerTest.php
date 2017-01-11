<?php

use \Lesstif\Confluence\Answer\AnswerService;

class QuestionTest extends PHPUnit_Framework_TestCase
{
    public function testGetAnswers()
    {
        $answerId = null;

        //$this->markTestSkipped();

        $queryParam = [
            // the number of questions needed (10 by default)
            'limit' => 10,

            //the start index (0 by default)
            'start' => 0,
        ];

        try {
            $as = new AnswerService();

            $ans = $as->getAnswers('lesstif', $queryParam);

            foreach($ans as $a) {
                dump($a);
                $answerId = $a->id;
            }

        } catch (\Lesstif\Confluence\ConfluenceException $e) {
            $this->assertTrue(false, 'testSearch Failed : '.$e->getMessage());
        }

        return $answerId;
    }

    /**
     * @depends testGetAnswers
     */
    public function testGetAnswerDetail($answerId)
    {
        global $argv, $argc;

        // override command line parameter
        if ($argc > 2) {
            $answerId = $argv[2];
        }

        try {
            $as = new AnswerService();

            $a = $as->getAnswerDetail($answerId);

            dump($a);

        } catch (\Lesstif\Confluence\ConfluenceException $e) {
            $this->assertTrue(false, 'testGetAnswerDetail Failed : '.$e->getMessage());
        }

        return $answerId;
    }

    /**
     * @depends testGetAnswerDetail
     */
    public function testGetAnswerRelativeQuestion($answerId)
    {
        global $argv, $argc;

        // override command line parameter
        if ($argc > 2) {
            $answerId = $argv[2];
        }

        try {
            $as = new AnswerService();

            $a = $as->getQuestion($answerId);

            dump($a);

        } catch (\Lesstif\Confluence\ConfluenceException $e) {
            $this->assertTrue(false, 'testGetAnswerRelativeQuestion Failed : '.$e->getMessage());
        }
    }
}
