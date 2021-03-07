<?php namespace Lesstif\Confluence\Space;

class Space
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $key;

    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $type;

    /**
     * @var array
     */
    public $_links;

    /**
     * @var array
     */
    public $_expandable;
}