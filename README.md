# PHP Confluence Rest Client

Atlassian's Confluence & Confluence Question REST API Client.

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

# Confluence Rest API Documents
* Confluence Server REST API - https://developer.atlassian.com/confdev/confluence-server-rest-api
* latest server - https://docs.atlassian.com/atlassian-confluence/REST/latest-server/
* Confluence Question REST API - https://docs.atlassian.com/confluence-questions/rest/index.html