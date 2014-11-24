<?php

namespace PHPExtra\Proxy\SymfonyBridge;

use PHPExtra\Proxy\Http\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * The AbstractResponse class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class AbstractResponse extends SymfonyResponse implements ResponseInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBody()
    {
        return $this->getContent();
    }

    /**
     * {@inheritdoc}
     */
    public function getLength()
    {
        return strlen($this->getContent());
    }

    /**
     * {@inheritdoc}
     */
    public function isPrivate()
    {
        return $this->hasHeaderWithValue('Cache-Control', 'private');
    }

    /**
     * {@inheritdoc}
     */
    public function getDate()
    {
        $date = $this->headers->getDate('Date', null);
        if($date === null){
            $this->setDate(new \DateTime('now'));
        }

        return parent::getDate();
    }

    // request response shared methods

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
} 