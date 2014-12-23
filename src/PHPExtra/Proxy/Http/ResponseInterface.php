<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Http;

use PHPExtra\Proxy\Http\Message\OutgoingResponseInterface;

/**
 * The ResponseInterface class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface ResponseInterface extends OutgoingResponseInterface
{
    public function setMaxAge($ttl);

    public function getDate();

    public function setDate($now);

    public function hasHeaderWithValue($string, $string1);

    public function getTtl();

    public function isSuccessful();

    public function getMaxAge();

    public function getLength();
}