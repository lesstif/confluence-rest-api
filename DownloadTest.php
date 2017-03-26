<?php
require 'vendor/autoload.php';

use Lesstif\Confluence\Dumper;
use Lesstif\Confluence\Page\Page;
use Lesstif\Confluence\Page\PageService;

function save($dir, $fileName, $content){
    @mkdir($dir, 0755, true);
    $file = new SplFileObject($dir . '/' . $fileName, "w");
    $written = $file->fwrite($content);

    $file->fflush();

    echo "Wrote $fileName file";
}

$pageId = '59444134';

try {
    $ps = new PageService();

    $page = $ps->getChild($pageId);

    //@mkdir('산출물');
    foreach($page->children as $a) {
        print($a->title . "\n");
        //@mkdir('산출물' . '/' . $a->title);

        $tp = new PageService();

        $cps = $tp->getChild($a->id);
        foreach($cps->children as $c) {
            //Dumper::dd($c);
            $dir = '산출물' . '/' . $a->title . '/' . $c->title;

            save($dir, $c->title, 'aaa');

            print("\t" . $c->title . "\n");
        }
    }

} catch (\Lesstif\Confluence\ConfluenceException $e) {
    $this->assertTrue(false, 'testSearch Failed : '.$e->getMessage());
}

