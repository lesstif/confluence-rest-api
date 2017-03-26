<?php namespace Lesstif\Confluence\Page;

use Lesstif\Confluence\ConfluenceClient;

class PageService extends ConfluenceClient
{
    public $uri = '/api/content';

    public function getProperty($pageId)
    {
        $url = sprintf('%s/%d', $this->uri, $pageId);

        $ret = $this->exec($url);

        return $page = $this->json_mapper->map(
            json_decode($ret), new Page()
        );
    }
}