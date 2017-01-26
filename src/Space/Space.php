<?php namespace Lesstif\Confluence\Space;

class Space
{
    /**
     * @var integer the start point of the collection to return
     */
    public $start;

    /**
     * @var integer the limit of the number of spaces to return, this may be restricted by fixed system limits
     * (Default 25)
     */
    public $limit;

    /**
     * @var integer
     */
    public $size;

    /**
     * @var array a list of result
     */
    public $results;

    /**
     * @var array
     */
    public $_links;
}