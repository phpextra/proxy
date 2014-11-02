<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Storage;

use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\ResponseInterface;

/**
 * The InMemoryStorage for testing purposes
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class InMemoryStorage implements StorageInterface
{
    /**
     * @var \SplObjectStorage
     */
    private $storage;

    function __construct()
    {
        $this->storage = new \SplObjectStorage();
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(RequestInterface $request)
    {
        return $this->storage->offsetGet($request);
    }

    /**
     * {@inheritdoc}
     */
    public function has(RequestInterface $request)
    {
        return $this->storage->contains($request);
    }

    /**
     * {@inheritdoc}
     */
    public function save(RequestInterface $request, ResponseInterface $response, $lifetime = null)
    {
        $this->storage->offsetSet($request, $response);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(RequestInterface $request)
    {
        $this->storage->offsetUnset($request);

        return $this;
    }
}