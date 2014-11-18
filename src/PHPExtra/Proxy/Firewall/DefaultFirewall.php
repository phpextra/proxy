<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */
 
namespace PHPExtra\Proxy\Firewall;

use PHPExtra\Proxy\Http\RequestInterface;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * The DefaultFirewall class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class DefaultFirewall implements FirewallInterface, LoggerAwareInterface
{
    /**
     * @var array
     */
    private $allowedIps = array();

    /**
     * @var array
     */
    private $allowedDomains = array();

    /**
     * {@inheritdoc}
     */
    public function allowIp($ip)
    {
        $this->allowedIps[] = $ip;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function allowDomain($domain)
    {
        $this->allowedDomains[] = $this->normalize($domain);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed(RequestInterface $request)
    {
        if(!empty($this->allowedIps)){
            $clientIp = $this->normalize($request->getClientIp());

            if(!in_array($clientIp, $this->allowedIps)){
                return false;
            }
        }

        if(!empty($this->allowedDomains)){
            $domain = $this->normalize($request->getHost());

            if(!in_array($domain, $this->allowedDomains)){
                return false;
            }
        }

        return true;
    }

    /**
     * @param string $string
     *
     * @return string
     */
    private function normalize($string){
        return strtolower($string);
    }

    /**
     * Sets a logger instance on the object
     *
     * @param LoggerInterface $logger
     *
     * @return null
     */
    public function setLogger(LoggerInterface $logger)
    {
        // TODO: Implement setLogger() method.
    }
}