<?php

/**
 * Copyright (c) 2014 Paweł Łuczkiewicz <me@agares.info>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Exception;

/**
 * Class ServerException
 *
 * @author Paweł Łuczkiewicz <me@agares.info>
 */
class RemoteServerCommunicationErrorException extends ProxyException
{
    private $requestedUri;

    public function __construct($requestedUri, \Exception $previousException)
    {
        $this->requestedUri = $requestedUri;

        parent::__construct('Remote server communication error', 0, $previousException);
    }

    public function getRequestedUri()
    {
        return $this->requestedUri;
    }
}