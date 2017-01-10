<?php namespace Lesstif\Confluence\Question;

class Question
{
    public $id;

    public $title;

    public $url;

    /** @var \Lesstif\Confluence\Question\Author */
    public $author;

    public $friendlyDateAsked;

    public $dateAsked;

    public $answersCount;

    /** @var \Lesstif\Confluence\Question\Topic[] */
    public $topics;
}