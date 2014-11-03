<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Http;

/**
 * The Request class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class Request extends \Symfony\Component\HttpFoundation\Request implements RequestInterface
{
    use HttpMessageTrait;

    /**
     * @param string $uri
     * @param string $method
     * @param array  $parameters
     * @param array  $cookies
     * @param array  $files
     * @param array  $server
     * @param string $content
     *
     * @return RequestInterface
     */
    public static function create(
        $uri,
        $method = 'GET',
        $parameters = array(),
        $cookies = array(),
        $files = array(),
        $server = array(),
        $content = null
    ) {
        return parent::create($uri, $method, $parameters, $cookies, $files, $server, $content);
    }

    /**
     * Creates a new request with values from PHP's super globals.
     *
     * @return RequestInterface
     */
    public static function createFromGlobals()
    {
        return parent::createFromGlobals();
    }

    /**
     * {@inheritdoc}
     */
    public function getFingerprint()
    {
        return md5(
            $this->getMethod() . $this->getHttpHost() . $this->getRequestUri() . serialize($this->getPostParams()) . serialize($this->getQueryParams())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPostParams()
    {
        return $this->request->all();
    }
    /**
     * {@inheritdoc}
     */
    public function getQueryParams()
    {
        return $this->query->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxAge()
    {
        $maxAgeHeaders = $this->getHeader('Max-Age', null);
        return isset($maxAgeHeaders[0]) ? $maxAgeHeaders[0] : null;
    }
}