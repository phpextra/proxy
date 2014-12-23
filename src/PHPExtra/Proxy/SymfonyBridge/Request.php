<?php

namespace PHPExtra\Proxy\SymfonyBridge;

use Phly\Http\Stream;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * The Request class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class Request extends \PHPExtra\Proxy\Http\Request
{
    /**
     * @param SymfonyRequest $symfonyRequest
     */
    function __construct(SymfonyRequest $symfonyRequest)
    {
        parent::__construct(
            $symfonyRequest->getUri(),
            $symfonyRequest->getMethod(),
            $symfonyRequest->headers->all(),
            new Stream($symfonyRequest->getContent(true)),
            $symfonyRequest->server->all(),
            $symfonyRequest->cookies->all(),
            $symfonyRequest->query->all(),
            $symfonyRequest->request->all(),
            $symfonyRequest->files->all(),
            $symfonyRequest->attributes->all()
        );
    }
}