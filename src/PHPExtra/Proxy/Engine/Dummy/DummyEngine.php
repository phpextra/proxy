<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Engine\Dummy;

use PHPExtra\Proxy\Engine\ProxyEngineInterface;
use Psr\Log\LoggerInterface;
use PHPExtra\Proxy\Http\RequestInterface;

/**
 * Dummy handler for testing purposes
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class DummyEngine implements ProxyEngineInterface
{
    /**
     * @var \Closure
     */
    private $handler = null;

    /**
     * @var LoggerInterface
     */
    private $logger = null;

    /**
     * Handler must accept RequestInterface as its the one and only parameter required
     *
     * @param \Closure $handler
     *
     * @return $this
     */
    public function setHandler($handler)
    {
        $this->handler = $handler;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(RequestInterface $request)
    {
        return call_user_func($this->handler, $request);
    }
}