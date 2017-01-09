<?php namespace Lesstif\Confluence\CQL;

use Lesstif\Confluence\ConfluenceClient;

class CQLService extends ConfluenceClient
{
    public $uri = '/content/search';

    public function search($paramArray = null)
    {
        $queryParam = '?' . 'cql=' . http_build_query($paramArray);

        $ret = $this->exec($this->uri . $queryParam, null);

        return $searchResults = $this->json_mapper->map(
            json_decode($ret), new CQLSearchResults()
        );
    }
}