<?php

namespace PHPExtra\Proxy\SymfonyBridge;

use PHPExtra\Proxy\Http\RequestInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * The AbstractRequest class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class AbstractRequest extends Request implements RequestInterface
{
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
} 