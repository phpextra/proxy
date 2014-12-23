<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Http;

use Phly\Http\OutgoingResponse;
use Psr\Http\Message\StreamableInterface;

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

    public function setHeaders(array $headers)
    {
        foreach($headers as $name => $value){
            $this->setHeader($name, $value);
        }
        return $this;
    }

    /**
     * @deprecated
     * @return \DateTime
     */
    public function getDate()
    {
        return new \DateTime();
    }

    /**
     * @deprecated
     * @param $now
     */
    public function setDate($now)
    {
    }

    public function hasHeaderWithValue($name, $value)
    {
        return $this->hasHeader($name) && in_array($value, explode(',', $this->getHeader($name)));
    }

    public function getTtl()
    {
        // TODO: Implement getTtl() method.
    }

    public function isSuccessful()
    {
        // TODO: Implement isSuccessful() method.
    }

    public function getMaxAge()
    {
        // TODO: Implement getMaxAge() method.
    }

    public function getLength()
    {
        // TODO: Implement getLength() method.
    }
}