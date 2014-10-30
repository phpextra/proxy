<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Event;

use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\ResponseInterface;

/**
 * Represents a proxy exception during request
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class ProxyExceptionEvent extends AbstractProxyEvent
{
    /**
     * @var \Exception
     */
    private $exception;

    /**
     * @param \Exception        $e
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     */
    function __construct(\Exception $e, RequestInterface $request, ResponseInterface $response = null)
    {
        $this->exception = $e;
        parent::__construct($request, $response);
    }

    /**
     * @return \Exception
     */
    public function getException()
    {
        return $this->exception;
    }
}