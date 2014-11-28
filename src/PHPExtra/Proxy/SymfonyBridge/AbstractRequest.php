<?php

namespace PHPExtra\Proxy\SymfonyBridge;

use PHPExtra\Proxy\Http\RequestInterface;
use Symfony\Component\HttpFoundation\Request as SymfonyRequest;

/**
 * The AbstractRequest class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
abstract class AbstractRequest extends SymfonyRequest implements RequestInterface
{
    /**
     * @var string
     */
    private $host;

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
     * Set host. This does not change the header value.
     *
     * @param string $host
     *
     * @return $this
     */
    public function setHost($host)
    {
        $this->host = $host;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHost()
    {
        if($this->host !== null){
            return $this->host;
        }

        return parent::getHost();
    }

    /**
     * @param string $host
     *
     * @return $this
     */
    public function setHostHeader($host)
    {
        $this->setHeader('host', $host);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHostHeader()
    {
        $host = $this->getHeader('host');
        return is_array($host) && isset($host[0]) ? $host[0] : null;
    }

    /**
     * {@inheritdoc}
     */
    public function setPostParams(array $params)
    {
        foreach($params as $name => $value){
            $this->request->set($name, $value);
        }
        return $this;
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
    public function getUri()
    {
        return parent::getUri();
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
        return $this->cookies->all();
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
    public function setHeaders(array $headers)
    {
        foreach($headers as $name => $value){
            $this->setHeader($name, $value);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeader($name, $default = null)
    {
        $header = $this->headers->get($name, $default, false);
        if(is_string($header)){
            $header = array($header);
        }
        return $header;
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

    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return parent::getContent(false);
    }
} 