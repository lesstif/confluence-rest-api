<?php namespace Lesstif\Confluence\Page;

use Lesstif\Confluence\ConfluenceClient;
use Symfony\Component\VarDumper\VarDumper;

class PageService extends ConfluenceClient
{
    public $uri = '/api/content';

    /**
     * @param $pageOrAttachmentId
     * @return Lesstif\Confluence\Page\Page
     * @throws \JsonMapper_Exception
     * @throws \Lesstif\Confluence\ConfluenceException
     */
    public function getPage($pageOrAttachmentId)
    {
        $url = sprintf('%s/%s', $this->uri, $pageOrAttachmentId);

        $ret = $this->exec($url);

        return $page = $this->json_mapper->map(
            json_decode($ret), new Page()
        );
    }

    public function updatePage($pageOrAttachmentId)
    {
        $url = sprintf('%s/%s', $this->uri, $pageOrAttachmentId);

        $ret = $this->exec($url);

        return $page = $this->json_mapper->map(
            json_decode($ret), new Page()
        );
    }

    public function deletePage($pageOrAttachmentId)
    {
        $url = sprintf('%s/%s', $this->uri, $pageOrAttachmentId);

        $ret = $this->exec($url, null, 'DELETE');

        return $this->http_response;
    }

    public function getChild($pageId)
    {
        $p = new Page();

        // get attachements
        $url = sprintf('%s/%s/child/attachment', $this->uri, $pageId);

        $ret = $this->exec($url);

        $atts = json_decode($ret);

        $p->attachments = $this->json_mapper->mapArray(
            $atts->results,  new \ArrayObject(), '\Lesstif\Confluence\Page\Attachment'
        );

        // child page
        $url = sprintf('%s/%s/child/page', $this->uri, $pageId);

        $ret = $this->exec($url);
        $children = json_decode($ret);

        $p->children = $children->results;

        return $p;
    }
}