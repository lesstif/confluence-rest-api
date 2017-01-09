# PHP Confluence Rest Client

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


# Confluence Rest API Documents
* Confluence Server REST API - https://developer.atlassian.com/confdev/confluence-server-rest-api
* latest server - https://docs.atlassian.com/atlassian-confluence/REST/latest-server/