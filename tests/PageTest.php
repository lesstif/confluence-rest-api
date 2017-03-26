<?php

use \Lesstif\Confluence\Page\PageService;

class PageTest extends PHPUnit_Framework_TestCase
{
    public function testGetPage()
    {
        $pageId = '59446186';

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
            $ps = new PageService();

            $p = $ps->getProperty($pageId);

            dump($p);

        } catch (\Lesstif\Confluence\ConfluenceException $e) {
            $this->assertTrue(false, 'testGetSpace Failed : '.$e->getMessage());
        }

        return 0;
    }

}
