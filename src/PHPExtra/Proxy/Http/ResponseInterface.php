<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Http;

/**
 * The ResponseInterface class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface ResponseInterface
{
    public function getMaxAge();

    public function getTtl();

    public function getLength();

    public function getStatusCode();

    public function getBody();

    /**
     * @return string
     */
    public function getCharset();

    public function isSuccessful();

    public function isRedirection();

    public function isClientError();

    public function isOk();

    public function isNotFound();

    public function isForbidden();

    public function isEmpty();

    public function isRedirect();

    /**
     * Returns true if age > 0
     *
     * @return bool
     */
    public function isFresh();

    /**
     * @deprecated
     * @return bool
     */
    public function isCacheable();

    /**
     * Returns true if cache is private
     *
     * @return bool
     */
    public function isPrivate();

    public function getAge();

    public function expire();

    public function getExpires();

    public function addHeader($name, $value);

    public function removeHeader($name);

    public function hasHeader($name);

    public function getHeader($name, $default = null);
}