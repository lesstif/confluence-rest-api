<?php

use Lesstif\Confluence\Page\Page;
use \Lesstif\Confluence\Page\PageService;

class AttachmentTest extends PHPUnit_Framework_TestCase
{
    public function testGetAttachment()
    {
        global $argv, $argc;

        $pageId = '59452396';

        // override command line parameter
        if ($argc === 3) {
            $pageId = $argv[2];
        }

        try {
            $ps = new PageService();

            $p = $ps->downloadAttachments($pageId, "d:\\");

            dump($p);

        } catch (\Lesstif\Confluence\ConfluenceException $e) {
            $this->assertTrue(false, 'testGetSpace Failed : '.$e->getMessage());
        }

        return $pageId;
    }

    /**
     * @depends testGetAttachment
     */
    public function testGetChildPage($attId)
    {
        try {
            $ps = new PageService();

            $p = $ps->getChild($attId);

            //print attachments
            $i = 0;
            foreach($p->attachments as $a) {
                if ($i++ > 3)
                    break;

                dump($a);
            }

        } catch (\Lesstif\Confluence\ConfluenceException $e) {
            $this->assertTrue(false, 'testGetSpace Failed : '.$e->getMessage());
        }

        return $attId;
    }
}
