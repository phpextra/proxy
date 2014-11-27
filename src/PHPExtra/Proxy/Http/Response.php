<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Http;

use Phly\Http\OutgoingResponse;

/**
 * The Response class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class Response extends OutgoingResponse implements ResponseInterface
{
    public function setMaxAge($ttl)
    {
        $this->setHeader('Cache-control', sprintf('max-age=%s, s-maxage=%s', $ttl, $ttl));
    }
}