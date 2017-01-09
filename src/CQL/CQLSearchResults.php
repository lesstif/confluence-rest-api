<?php namespace Lesstif\Confluence\CQL;


class CQLSearchResults
{
    /** @var \Lesstif\Confluence\CQL\SearchResult[] */
    public $results;

    /** @var int */
    public $start = 0;

    /** @var int  */
    public $limit = 25;

    /** @var int  */
    public $size = 10;

    /** @var  array|null */
    public $_links;
}