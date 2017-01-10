<?php

use \Lesstif\Confluence\Question\QuestionService;

class QuestionTest extends PHPUnit_Framework_TestCase
{
    public function testGetQuestion()
    {
        //$this->markTestSkipped();

        $queryParam = [
            // the number of questions needed (10 by default)
            'limit' => 10,

            //the start index (0 by default)
            'start' => 0,

            // The optional filter string which value is one of "unanswered", "popular", "my", "recent"
            // (default value 'recent')
            'filter' => 'unanswered',
        ];

        try {
            $qs = new QuestionService();

            $questions = $qs->getQuestion($queryParam);

            foreach($questions as $q) {
                echo sprintf("<a href=\"%s\">%s</a><p/>\n", $q->url, $q->title);
            }

        } catch (\Lesstif\Confluence\ConfluenceException $e) {
            $this->assertTrue(false, 'testSearch Failed : '.$e->getMessage());
        }
    }
}
