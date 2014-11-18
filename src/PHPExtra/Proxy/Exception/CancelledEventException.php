<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */
 
namespace PHPExtra\Proxy\Exception;

use PHPExtra\Proxy\Event\ProxyEventInterface;

/**
 * The CancelledEventException class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class CancelledEventException extends ProxyException
{
    /**
     * @var ProxyEventInterface
     */
    private $event;

    /**
     * @param ProxyEventInterface $event
     * @param \Exception          $previous
     */
    function __construct(ProxyEventInterface $event, \Exception $previous = null)
    {
        $this->event = $event;
        parent::__construct(sprintf('Event (%s) was cancelled', get_class($event)), 1, $previous);
    }

    /**
     * @return ProxyEventInterface
     */
    public function getEvent()
    {
        return $this->event;
    }
}