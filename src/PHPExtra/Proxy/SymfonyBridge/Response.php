<?php

namespace PHPExtra\Proxy\SymfonyBridge;

use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * The Request class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class Response extends AbstractResponse
{
    /**
     * @param SymfonyResponse $symfonyResponse
     */
    function __construct(SymfonyResponse $symfonyResponse)
    {
        parent::__construct($symfonyResponse->getContent(), $symfonyResponse->getStatusCode(), $symfonyResponse->headers->all());
    }
}