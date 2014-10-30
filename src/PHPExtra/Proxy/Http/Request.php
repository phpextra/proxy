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
    /**
     * {@inheritdoc}
     */
    public function getFingerprint()
    {
        return md5(
            $this->getMethod() . $this->getHttpHost() . $this->getRequestUri() . serialize($this->getPostParams()) . serialize(
                $this->getQueryParams()
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getCookies()
    {
        return $this->cookies->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->headers->all();
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
    public function addHeader($name, $value)
    {
        $this->headers->set($name, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function removeHeader($name)
    {
        $this->headers->remove($name);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name, $default = null)
    {
        return $this->headers->get($name, $default);
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return $this->headers->has($name);
    }

    /**
     * @param string $uri
     * @param string $method
     * @param array  $parameters
     * @param array  $cookies
     * @param array  $files
     * @param array  $server
     * @param string $content
     *
     * @return $this
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
}