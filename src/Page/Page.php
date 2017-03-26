<?php

namespace Lesstif\Confluence\Page;


class Page
{
    /**
     * @var integer
     */
    public $id;

    /** @var string */
    public $type;

    /** @var string */
    public $status;

    /** @var string */
    public $title;

    /** @var \Lesstif\Confluence\Space\Space */
    public $space;

    /** @var \Lesstif\Confluence\Page\History */
    public $history;

    /** @var \Lesstif\Confluence\Page\Attachment[] */
    public $attachments;

    /** @var array */
    public $children;
}