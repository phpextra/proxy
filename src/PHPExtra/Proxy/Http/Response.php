<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Http;

/**
 * The Response class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class Response extends \Symfony\Component\HttpFoundation\Response implements ResponseInterface
{
    use HttpMessageTrait;

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

    /**
     * {@inheritdoc}
     */
    public function getExpireDate()
    {
        //@todo think about using age directive ?
        if($this->hasHeader('Max-Age')){
            $maxAgeInterval = new \DateInterval(sprintf('P%sS', $this->getMaxAge()));
            $date = $this->getDate()->add($maxAgeInterval);
        }else{
            $date = $this->getExpires();
        }

        return $date;
    }

    /**
     * {@inheritdoc}
     */
    public function send()
    {
        return parent::send();
    }
}