<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */
 
namespace PHPExtra\Proxy\Http;

use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * The AbstractHttpMessage class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
trait HttpMessageTrait
{
    /**
     * @var ParameterBag
     */
    public $cookies;

    /**
     * @var HeaderBag
     */
    public $headers;

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
    public function addHeader($name, $value)
    {
        $this->headers->add(array($name => $value));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeader($name, $value)
    {
        $this->headers->set($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name, $default = null)
    {
        return $this->headers->get($name, $default, false);
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
    public function getHeaders()
    {
        return $this->headers->all();
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
    public function hasHeaderWithValue($name, $value)
    {
        return $this->headers->contains($name, $value);
    }
}