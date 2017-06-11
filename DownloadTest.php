<?php
require 'vendor/autoload.php';

use Lesstif\Confluence\ConfluenceClient;
use Lesstif\Confluence\Dumper;
use Lesstif\Confluence\Page\Page;
use Lesstif\Confluence\Page\PageService;

function save($dir, $fileName, $content){
    @mkdir($dir, 0755, true);
    $file = new SplFileObject($dir . '/' . $fileName, "w");
    $written = $file->fwrite($content);

    $file->fflush();

    echo " $fileName\n";
}

function download($url, $dir, $fileName){
    print("download " . $url);

    $c = new ConfluenceClient();

    $data = $c->exec($url, null, null, true);

    /*

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_USERPWD, "lesstif:dbsdn07dbwn09");
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $data = curl_exec ($ch);

    Dumper::dd($data);

    curl_close ($ch);
    */

    save($dir, $fileName, $data);
}

function downLoadAttachment($pageId)
{
    $ps = new PageService();

    $page = $ps->getChild($pageId);

    foreach ($page->children as $a) {
        print($a->title . "\n");

        $tp = new PageService();

        $cps = $tp->getChild($a->id);

        foreach ($cps->children as $c) {

            $parent = str_replace("/", ",", $a->title);
            $child = str_replace("/", ",", $c->title);

            $path = $parent . '/' . $child;

            if (strpos($a->title, '관리') !== false) { // 관리 산출물
                $path = '산출물' . '/' . $path;

                // 또 가져오기
                $cp = new PageService();

                $tmp = $cp->getChild($c->id);

                foreach ($tmp as $att) {

                    $att = $tp->getPage($a->id);

                    download($att->download, $path, $a->title);

                    //save($path, $cps, 'bbb');
                }

                print("\t" . $c->title . "\n");
            } elseif (strpos($a->title, '개발') !== false) { // 개발 산출물
                $dir = '산출물' . '/' . $dir;

                save($dir, $file, 'aaa');

                print("\t" . $c->title . "\n");
            } else {
                die("모르는 산출물 " . $a->title);
            }

        }

    }
} // download


//$pageId = '59444134';

$ids = ['59446136', '59446138'];

foreach($ids as $id) {

    $me = (new PageService())->getPage($id);

    $page = (new PageService())->getChild($id);

    $isDev = 0;

    //Dumper::dd($page);

    foreach ($page->children as $firstChild) {

        $path = '산출물/' . $me->title . '/' . $firstChild->title;



        if (strpos($me->title, 'MG1') !== false) { // 관리 산출물
            Dumper::dump($me->title . '/' . $firstChild->title);

            $prjCode = preg_replace("/[^A-Za-z0-9\-]/", "", $firstChild->title);

            $secondChild = (new PageService())->getChild($firstChild->id);
            //Dumper::dump(['id' => $firstChild->id,'count ' => count($secondChild)]);

            // 여기부터 첨부 파일
            foreach($secondChild->attachments as $att) {
                //Dumper::dump($att);
                //$attaches = (new PageService())->getChild($c->id);

                //foreach ($attaches as $att) {
                    //Dumper::dd($cc);

                    //$attachs = (new PageService())->getChild($cc->id);

                    //foreach($attachs->attachments as $att) {
                    $url = 'https://wiki.ktnet.com/' . $att->_links->download;

                    download($url, $path, 'SF-2017-DV-' . $prjCode . ' ' . $att->title);
                    //Dumper::dd($att);
                    //}
                //}
            }


            //downLoadAttachment($a->id);

            //save($path, 'qwe', 'accd');

            //Dumper::dd('qwe');
        }
        else if (strpos($me->title, 'DV') !== false) {  // 개발 산출물
            Dumper::dump($me->title . '/' . $firstChild->title);

            $secondChild = (new PageService())->getChild($firstChild->id);

            // 한 단계 더 아래
            foreach($secondChild->children as $sc) {

                $prjCode = preg_replace("/[^A-Za-z0-9\-]/", "", $secondChild->title);

                Dumper::dump($sc);
                $attaches = (new PageService())->getChild($sc->id);

                (new PageService())->downloadAttachments($sc->id, "abc");

                foreach ($attaches as $att) {
                    //Dumper::dd($att);


                    //$attachs = (new PageService())->getChild($cc->id);

                    //foreach($attachs->attachments as $att) {
                    //$url = 'https://wiki.ktnet.com/' . $att->_links->download;

                    //download($url, $path, 'SF-2017-DV-' . $prjCode . ' ' . $att->title);
                    //Dumper::dd($att);
                    //}
                    //}
                }
            }
        }
        else { //몰름
            //var_dump("Unknown title :" . $me->title);

        }

    }
}
