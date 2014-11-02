<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Http;

/**
 * The Response class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class Response extends \Symfony\Component\HttpFoundation\Response implements ResponseInterface
{
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
    public function getBody()
    {
        return $this->getContent();
    }

    /**
     * {@inheritdoc}
     */
    public function getLength()
    {
        return strlen($this->getContent());
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return $this->headers->has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name, $default = null)
    {
        return $this->headers->get($name, $default);
    }

    /**
     * Returns true if cache is private
     *
     * @return bool
     */
    public function isPrivate()
    {
        return $this->getHeader('cache-control') == 'private';
    }
}