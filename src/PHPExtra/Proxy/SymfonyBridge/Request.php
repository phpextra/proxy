<?php

namespace PHPExtra\Proxy\SymfonyBridge;

use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * The Request class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class Request extends AbstractRequest
{
    /**
     * @var SymfonyRequest
     */
    private $symfonyRequest;

    /**
     * @param SymfonyRequest $symfonyRequest
     */
    function __construct(SymfonyRequest $symfonyRequest)
    {
        $this->symfonyRequest = $symfonyRequest;

        $this->request = $symfonyRequest->request;
        $this->query = $symfonyRequest->query;
        $this->attributes = $symfonyRequest->attributes;
        $this->cookies = $symfonyRequest->cookies;
        $this->files = $symfonyRequest->files;
        $this->server = $symfonyRequest->server;
        $this->headers = $symfonyRequest->headers;

        $this->content = $symfonyRequest->content;
        $this->languages = $symfonyRequest->languages;
        $this->charsets = $symfonyRequest->charsets;
        $this->encodings = $symfonyRequest->encodings;
        $this->acceptableContentTypes = $symfonyRequest->acceptableContentTypes;
        $this->pathInfo = $symfonyRequest->pathInfo;
        $this->requestUri = $symfonyRequest->requestUri;
        $this->baseUrl = $symfonyRequest->baseUrl;
        $this->basePath = $symfonyRequest->basePath;
        $this->method = $symfonyRequest->method;
        $this->format = $symfonyRequest->format;
    }
}