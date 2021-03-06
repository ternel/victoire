<?php
namespace Victoire\Bundle\PageBundle\Helper;

use Symfony\Component\HttpFoundation\RequestStack;
use Victoire\Bundle\PageBundle\Repository\PageRepository;

/**
 * ref: victoire_page.url_helper
 */
class UrlHelper
{
    protected $request = null;
    protected $router = null;
    protected $pageRepository = null;

    /**
     * Constructor
     * @param unknown        $router
     * @param PageRepository $pageRepository
     */
    public function __construct($router, PageRepository $pageRepository)
    {
        $this->router = $router;
        $this->pageRepository = $pageRepository;
    }

    /**
     * Set the current request
     *
     * @param RequestStack $requestStack
     */
    public function setRequest(RequestStack $requestStack)
    {
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * Get the urlMatcher for the template generator
     * It removes what is after the last /
     * and add /{id} to the url
     * @param string $url
     *
     * @return string The url
     */
    public function getGeneralUrlMatcher($url)
    {
        $urlMatcher = null;

        // split on the / character
        $keywords = preg_split("/\//", $url);

        //if there are some words, we pop the last
        if (count($keywords) > 0) {
            array_pop($keywords);
        }

        //add the id to the end of the url
        array_push($keywords, '{{item.id}}');

        //rebuild the url
        $urlMatcher = implode('/', $keywords);

        return $urlMatcher;
    }

    /**
     * Get the entity id from the url
     * @param string $url
     *
     * @return string The id
     */
    public function getEntityIdFromUrl($url)
    {
        $entityId = null;

        // split on the / character
        $keywords = preg_split("/\//", $url);

        //if there are some words, we pop the last
        if (count($keywords) > 0) {
            $entityId = array_pop($keywords);
        }

        return $entityId;
    }

    /**
     * Get the url called in the page from the referer of an ajax call
     *
     * @return string
     */
    public function getAjaxUrlRefererWithoutBase()
    {
        $request = $this->request;

        //get the base url
        $router = $this->router;

        //the referer
        $referer = urldecode($request->server->get('HTTP_REFERER'));

        // split on the / character
        $keywords = preg_split("/\//", $referer);

        //it gives an array that looks like
        //    [0] => http:
        //    [1] =>
        //    [2] => sandbox.dev:443
        //    [3] => renault
        //    [4] => clio

        unset($keywords[0]);
        unset($keywords[1]);
        unset($keywords[2]);

        //so we remove the 3 first entries
        $urlReferer = implode('/', $keywords);

        //remove potential parameters
        $position = stripos($urlReferer, "?");
        if ($position > 0) {
            $urlReferer = substr($urlReferer, 0, stripos($urlReferer, "?"));
        }

        return $urlReferer;
    }

    /**
     * Is this url is already used
     * @param string  $url     The url to test
     * @param integer $suffixe The suffixe
     *
     * @return string The next available url
     */
    public function getNextAvailaibleUrl($url, $suffixe = 1)
    {
        $isUrlAlreadyUsed = $this->isUrlAlreadyUsed($url);

        //if the url is alreay used, we look for another one
        if ($isUrlAlreadyUsed) {
            $urlWithSuffix = $url.'-'.$suffixe;

            $isUrlAlreadyUsed = $this->isUrlAlreadyUsed($urlWithSuffix);

            //the url is still used, we try the next one
            if ($isUrlAlreadyUsed) {
                //get the next available url
                $url = $this->getNextAvailaibleUrl($url, $suffixe + 1);
            } else {
                //this url if free, let us use it
                $url = $urlWithSuffix;
            }
        }

        return $url;
    }

    /**
     * Test is the url is already used
     * @param string $url
     *
     * @return boolean Is the url free
     */
    public function isUrlAlreadyUsed($url)
    {
        $isUrlAlreadyUsed = false;

        //try to get a page with this url
        $page = $this->pageRepository->findOneByUrl($url);

        //a page use this url
        if ($page !== null) {
            $isUrlAlreadyUsed = true;
        }

        return $isUrlAlreadyUsed;
    }

    /**
     * Remove the last part of the url
     * @param string $url
     *
     * @return string The shorten url
     */
    public function removeLastPart($url)
    {
        $shortenUrl = null;

        if ($url !== null && $url !== '') {
            // split on the / character
            $keywords = preg_split("/\//", $url);

            //if there are some words, we pop the last
            if (count($keywords) > 0) {
                array_pop($keywords);

                //rebuild the url
                $shortenUrl = implode('/', $keywords);
            }
        }

        return $shortenUrl;
    }

    /**
     * Extract a part of the url
     * @param string  $url
     * @param integer $position
     *
     * @return string The extracted part
     */
    public function extractPartByPosition($url, $position)
    {
        $part = null;

        if ($url !== null && $url !== '') {
            // split on the / character
            $keywords = preg_split("/\//", $url);
            // preg_match_all('/\{\%\s*([^\%\}]*)\s*\%\}|\{\{\s*([^\}\}]*)\s*\}\}/i', $url, $matches);

            //if there are some words, we pop the last
            if (count($keywords) > 0) {
                //get the part
                $part = $keywords[$position - 1];
            }
        }

        return $part;
    }
}
