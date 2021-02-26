<?php namespace Lesstif\Confluence\Question;

class Question
{
    public $id;

    public $title;

    public $url;

    /** @var \Lesstif\Confluence\Question\Author */
    public $author;

    public $friendlyDateAsked;

    /** @var \DateTimeInterface */
    public $dateAsked;

    /** @var int */
    public $answersCount;

    /** @var \Lesstif\Confluence\Question\Topic[] */
    public $topics;

    /** @var string */
    public $acceptedAnswerId;
}
