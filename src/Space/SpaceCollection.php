<?php

namespace Lesstif\Confluence\Space;

class SpaceCollection
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
     * @var Space[]
     */
    public $results;

    /**
     * @var array
     */
    public $_links;
}