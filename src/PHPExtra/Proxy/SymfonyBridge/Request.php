<?php

namespace PHPExtra\Proxy\SymfonyBridge;

use PHPExtra\Proxy\Http\HttpMessageTrait;
use PHPExtra\Proxy\Http\RequestInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * The Request class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class Request extends SymfonyRequest implements RequestInterface
{
    use HttpMessageTrait;

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

    /**
     * {@inheritdoc}
     */
    public function getFingerprint()
    {
        return md5(
            $this->getMethod() . $this->getHttpHost() . $this->getRequestUri() . serialize($this->getPostParams()) . serialize($this->getQueryParams())
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getPostParams()
    {
        return $this->request->all();
    }
    /**
     * {@inheritdoc}
     */
    public function getQueryParams()
    {
        return $this->query->all();
    }

    /**
     * {@inheritdoc}
     */
    public function getMaxAge()
    {
        $maxAgeHeaders = $this->getHeader('Max-Age', null);
        return isset($maxAgeHeaders[0]) ? $maxAgeHeaders[0] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function getCookies()
    {
    }

    // request and response shared code

    /**
     * {@inheritdoc}
     */
    public function addHeader($name, $value)
    {
        $this->headers->add(array($name => $value));

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeader($name, $value)
    {
        $this->headers->set($name, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name, $default = null)
    {
        return $this->headers->get($name, $default, false);
    }

    /**
     * {@inheritdoc}
     */
    public function removeHeader($name)
    {
        $this->headers->remove($name);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeaders()
    {
        return $this->headers->all();
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeader($name)
    {
        return $this->headers->has($name);
    }

    /**
     * {@inheritdoc}
     */
    public function hasHeaderWithValue($name, $value)
    {
        return $this->headers->contains($name, $value);
    }

    // overriden
}