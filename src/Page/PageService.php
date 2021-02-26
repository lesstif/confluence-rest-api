<?php namespace Lesstif\Confluence\Page;

use Lesstif\Confluence\ConfluenceClient;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Local;

/**
 * Class PageService
 * @package Lesstif\Confluence\Page
 */
class PageService extends ConfluenceClient
{
    public $uri = '/api/content';

    /**
     * @param string $pageOrAttachmentId
     * @return \Lesstif\Confluence\Page\Page
     * @throws \JsonMapper_Exception
     * @throws \Lesstif\Confluence\ConfluenceException
     */
    public function getPage(string $pageOrAttachmentId) : \Lesstif\Confluence\Page\Page
    {
        $url = sprintf('%s/%s', $this->uri, $pageOrAttachmentId);

        $ret = $this->get($url);

        return $page = $this->json_mapper->map(
            json_decode($ret), new Page()
        );
    }

    /**
     * @param string $pageOrAttachmentId
     * @return Page
     * @throws \JsonMapper_Exception
     * @throws \Lesstif\Confluence\ConfluenceException
     */
    public function updatePage(string $pageOrAttachmentId): Page
    {
        $url = sprintf('%s/%s', $this->uri, $pageOrAttachmentId);

        $ret = $this->exec($url);

        return $page = $this->json_mapper->map(
            json_decode($ret), new Page()
        );
    }

    /**
     * @param string $pageOrAttachmentId
     * @return int|string
     * @throws \Lesstif\Confluence\ConfluenceException
     */
    public function deletePage(string $pageOrAttachmentId)
    {
        $url = sprintf('%s/%s', $this->uri, $pageOrAttachmentId);

        $ret = $this->exec($url, null, 'DELETE');

        return $this->http_response;
    }

    /**
     * @param string $pageId
     * @return Page
     * @throws \JsonMapper_Exception
     * @throws \Lesstif\Confluence\ConfluenceException
     */
    public function getChildPage(string $pageId) : Page
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
     * @param string $pageId
     * @return Page
     * @throws \Lesstif\Confluence\ConfluenceException
     */
    public function getAttachmentList(string $pageId) : Page
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
     * @param string $pageId
     * @param string $destination output directory
     * @return array array of downloaded file list.
     * @throws \Lesstif\Confluence\ConfluenceException
     *
     * TODO
     */
    public function downloadAttachments(string $pageId, string $destination = '.' ) : array
    {
        $page = $this->getPage($pageId);

        // get attachements
        $url = sprintf('%s/%s/child/attachment', $this->uri, $pageId);

        $ret = $this->exec($url);

        $atts = json_decode($ret);

        $attachments = $this->json_mapper->mapArray(
            $atts->results,  new \ArrayObject(), '\Lesstif\Confluence\Page\Attachment'
        );

        $files = [];

        foreach($attachments as $a) {
            array_push($files, $a->title);

            $url =  $this->getConfiguration()->getHost() . $a->_links->download;
            $content = $this->exec($url, null, null, true);

            $adapter = new \League\Flysystem\Local\LocalFilesystemAdapter($destination);
            $filesystem = new Filesystem($adapter);

            $fileName = $a->title;
            if (defined('PHP_WINDOWS_VERSION_MAJOR') === true) {
                $fileName = iconv('UTF-8', 'CP949', $fileName);
            }

            $filesystem->write($page->title . DIRECTORY_SEPARATOR . $fileName, $content);

        }

        return $files;
    }
}