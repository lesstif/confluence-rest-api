<?php namespace Lesstif\Confluence\Space;

use Lesstif\Confluence\ConfluenceClient;
use Lesstif\Confluence\ConfluenceException;

use Lesstif\Confluence\Constants;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Confluence Space REST Service class
 *
 * @package Lesstif\Confluence\Space
 */
class SpaceService extends ConfluenceClient
{
    // override parent uri
    public $url = '/' ;

    private $defaultParam = [
                    'limit' => 25,
                    'start' => 0,
                    'type' => 'global',
                    'status' => 'current',
                    ];

    /**
     * get question list
     *
     * @param array $spaceKeyArray a list of space keys
     * @param string|null $paramArray parameter array
     * @return mixed
     * @throws \Lesstif\Confluence\ConfluenceException
     */
    public function getSpace(array $spaceKeyArray, string $paramArray = null)
    {
        // set default param
        if (empty($paramArray))
        {
            $paramArray = $this->defaultParam;
        }

        $queryParam = null;
        if (!empty($spaceKeyArray)) {
            $spaceParam = '&';
            foreach ($spaceKeyArray as $k) {
                $spaceParam = $spaceParam . 'spaceKey=' . $k . '&';
            }

            $queryParam = 'api/space?' . $spaceParam;
        } else {
            $queryParam = 'api/space?' . http_build_query($paramArray);
        }

        $ret = $this->exec($this->url . $queryParam, null);

        $ar = json_decode($ret);

        return $searchResults = $this->json_mapper->mapArray(
            $ar->results,  new \ArrayObject(), '\Lesstif\Confluence\Space\Space'
        );
    }

}