<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Http;

use Phly\Http\IncomingRequest;

/**
 * The Request class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class Request extends IncomingRequest
{
    /**
     * @param $uri
     *
     * @return $this
     */
    public static function create($uri)
    {
        return new static($uri);
    }
}