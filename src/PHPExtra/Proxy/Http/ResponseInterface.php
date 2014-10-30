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

    public function getCharset();

    public function isInvalid();

    public function isInformational();

    public function isSuccessful();

    public function isRedirection();

    public function isClientError();

    public function isOk();

    public function isNotFound();

    public function isForbidden();

    public function isEmpty();

    public function isRedirect();

    public function isFresh();

    public function isCacheable();

    public function getAge();

    public function expire();

    public function getExpires();

    public function addHeader($name, $value);

    public function removeHeader($name);

    public function hasHeader($name);

    public function getHeader($name, $default = null);
}