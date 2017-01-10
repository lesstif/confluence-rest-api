# PHP Confluence Rest Client

Atlassian's Confluence & Confluence Question REST API Client for PHP Users.

## installation

```sh
composer require 'lesstif/confluence-rest-api'
```

or update your composer.json.

```
"require": {
    "lesstif/confluence-rest-api": "^0.1"
}
```

## Configuration

you can choose loads environment variables either 'dotenv' or 'array'.

### use dotenv

copy .env.example file to .env on your project root directory.

CONFLUENCE_HOST="https://your-confluence.host.com"
CONFLUENCE_USER="confluence-username"
CONFLUENCE_PASS="confluence-password"

### use array

create Service class with ArrayConfiguration parameter.

use Lesstif\Confluence\Question\QuestionService;

$qs = new QuestionService(new \Lesstif\Confluence\Configuration\ArrayConfiguration(
          [
              'host' => 'https://your-confluence.host.com',
              'user' => 'confluence-username',
              'password' => 'confluence-password',
          ]
   ));

# Usage

## CQL

```php
$cql = [
    'SPACE' => 'LAR',
    'type' => 'page',
    ];

try {
    $s = new CQLService();

    $ret = $s->search($cql);

    dump($ret);

} catch (\Lesstif\Confluence\ConfluenceException $e) {
    $this->assertTrue(false, 'testSearch Failed : '.$e->getMessage());
}
```

## Question

### get Question list

```php

$queryParam = [
    // the number of questions needed (10 by default)
    'limit' => 10,

    //the start index (0 by default)
    'start' => 0,

    // The optional filter string which value is one of "unanswered", "popular", "my", "recent"
    // (default value 'recent')
    'filter' => 'unanswered',
];

try {
    $qs = new QuestionService();

    $questions = $qs->getQuestion($queryParam);

    foreach($questions as $q) {
        echo sprintf("<a href=\"%s\">%s</a><p/>\n", $q->url, $q->title);
    }

} catch (\Lesstif\Confluence\ConfluenceException $e) {
    $this->assertTrue(false, 'testSearch Failed : '.$e->getMessage());
}

```

### get Question's detail info.

```php
try {
    $qs = new QuestionService();

    $q = $qs->getQuestionDetail($questionId);

    foreach($q->answers as $a)
    {
        // print accepted answer
        if ($a->accepted === true) {
            dump($a);
        }
    }

} catch (\Lesstif\Confluence\ConfluenceException $e) {
    $this->assertTrue(false, 'testSearch Failed : '.$e->getMessage());
}
```

# Confluence Rest API Documents
* Confluence Server REST API - https://developer.atlassian.com/confdev/confluence-server-rest-api
* latest server - https://docs.atlassian.com/atlassian-confluence/REST/latest-server/
* Confluence Question REST API - https://docs.atlassian.com/confluence-questions/rest/index.html