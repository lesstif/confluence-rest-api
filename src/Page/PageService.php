<?php namespace Lesstif\Confluence\Page;

use Lesstif\Confluence\ConfluenceClient;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

class PageService extends ConfluenceClient
{
    public $uri = '/api/content';

    /**
     * @param $pageOrAttachmentId
     * @return \Lesstif\Confluence\Page\Page
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

    public function getChildPage($pageId)
    {
        $p = new Page();

        // get attachements
        $url = sprintf('%s/%s/child/page', $this->uri, $pageId);

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

    /**
     * get current page's attachements list
     *
     * @param $pageId
     * @return Page
     * @throws \Lesstif\Confluence\ConfluenceException
     */
    public function getAttachment($pageId)
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

    /**
     * download all attachment in the current page
     *
     * @param $pageId
     * @param $destination output directory
     * @return Page
     * @throws \Lesstif\Confluence\ConfluenceException
     */
    public function downloadAttachments($pageId, $destination = '.' )
    {
        // get attachements
        $url = sprintf('%s/%s/child/attachment', $this->uri, $pageId);

        $ret = $this->exec($url);

        $atts = json_decode($ret);

        $attachments = $this->json_mapper->mapArray(
            $atts->results,  new \ArrayObject(), '\Lesstif\Confluence\Page\Attachment'
        );

        foreach($attachments as $a) {
            $url =  $this->getConfiguration()->getHost() . $a->_links->download;
            $content = $this->exec($url, null, null, true);

            $adapter = new Local($destination);
            $filesystem = new Filesystem($adapter);

            $filesystem->write($a->title, $content);
        }

        return true;
    }
}