<?php

/**
 * Copyright (c) 2013 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Engine;

/**
 * Process given request and return appropriate response
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface EngineAwareInterface
{
    /**
     * Set proxy engine
     *
     * @param ProxyEngineInterface $engine
     *
     * @return $this
     */
    public function setEngine(ProxyEngineInterface $engine);
} 