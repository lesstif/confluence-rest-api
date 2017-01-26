<?php

use \Lesstif\Confluence\Space\SpaceService;

class SpaceTest extends PHPUnit_Framework_TestCase
{
    public function testGetSpace()
    {
        $questionId = null;

        //$this->markTestSkipped();

        $queryParam = [
            // the number of questions needed (25 by default)
            'limit' => 25,

            //the start index (0 by default)
            'start' => 0,

            // filter the list of spaces returned by type (global, personal)
            'type' => 'global',

            //filter the list of spaces returned by status (current, archived)
            'status' => 'current',
        ];

        try {
            $ss = new SpaceService();

            $spaces = $ss->getSpace(null);

            foreach($spaces as $s) {
                dump($s);
            }

        } catch (\Lesstif\Confluence\ConfluenceException $e) {
            $this->assertTrue(false, 'testGetSpace Failed : '.$e->getMessage());
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
