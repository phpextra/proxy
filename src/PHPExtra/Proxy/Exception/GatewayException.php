<?php

/**
 * Copyright (c) 2014 Paweł Łuczkiewicz <me@agares.info>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Exception;
use PHPExtra\Proxy\Http\RequestInterface;

/**
 * @author Paweł Łuczkiewicz <me@agares.info>
 */
class GatewayException extends ProxyException
{
    /**
     * @var RequestInterface
     */
    private $request;

    public function __construct(RequestInterface $request, \Exception $previousException)
    {
        $this->request = $request;

        parent::__construct('Remote server communication error', 0, $previousException);
    }

    public function getRequest()
    {
        return $this->request;
    }
}