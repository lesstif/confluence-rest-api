<?php

use Lesstif\Confluence\Page\Page;
use \Lesstif\Confluence\Page\PageService;
use Lesstif\Confluence\Dumper;

class PageTest extends PHPUnit_Framework_TestCase
{
    public function testGetPage()
    {
        global $argv, $argc;

        $pageId = '59444134';

        // override command line parameter
        if ($argc > 2) {
            $pageId = $argv[2];
        }

        try {
            $ps = new PageService();

            $p = $ps->getPage($pageId);

            //$this->assertClassNotHasAttribute('id', $p);
            //Dumper::dd($p);
        } catch (\Lesstif\Confluence\ConfluenceException $e) {
            $this->assertTrue(false, 'testGetSpace Failed : '.$e->getMessage());
        }

        return $pageId;
    }

    /**
     * @depends testGetPage
     */
    public function testGetChildPage($pageId)
    {
        try {
            $ps = new PageService();

            $p = $ps->getChild($pageId);

            //print attachments
            $i = 0;
            //foreach($p->attachments as $a) {
            foreach($p->children as $a) {
                if ($i++ > 3)
                    break;

                Dumper::dd($a);
                $ret = $ps->deletePage($a->id);
                dump($ret);
                break;
            }

        } catch (\Lesstif\Confluence\ConfluenceException $e) {
            $this->assertTrue(false, 'testGetSpace Failed : '.$e->getMessage());
        }

        return $pageId;
    }

}
