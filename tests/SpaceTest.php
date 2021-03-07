<?php

use Lesstif\Confluence\Question\QuestionService;
use \Lesstif\Confluence\Space\SpaceService;
use PHPUnit\Framework\TestCase;

class SpaceTest extends TestCase
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

        $spaces = [];

        try {
            $ss = new SpaceService();

            $sp = $ss->getSpace(null);

            $spaces[] = $sp;

            $this->assertNotEquals(0, $sp->size);

            while(true) {
                if (!empty($spaces->_links['next'])) {
                    $sp = $ss->getNext(null, $sp->_links['next']);
                    $spaces[] = $sp;
                } else {
                    break;
                }
            }

            dump($spaces);

        } catch (\Lesstif\Confluence\ConfluenceException $e) {
            $this->assertTrue(false, 'testGetSpace Failed : '.$e->getMessage());
        }

        return $questionId;
    }

}
