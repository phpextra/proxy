<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Storage;

use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\ResponseInterface;
use Doctrine\Common\Cache\Cache;

/**
 * The DoctrineStorage class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class DoctrineStorage implements StorageInterface
{
    /**
     * @var Cache
     */
    private $doctrineCacheAdapter;

    /**
     * @param Cache $doctrineCacheAdapter
     */
    function __construct(Cache $doctrineCacheAdapter)
    {
        $this->doctrineCacheAdapter = $doctrineCacheAdapter;
    }

    /**
     * {@inheritdoc}
     */
    public function fetch(RequestInterface $request)
    {
        return $this->doctrineCacheAdapter->fetch($request->getFingerprint());
    }

    /**
     * {@inheritdoc}
     */
    public function has(RequestInterface $request)
    {
        return $this->doctrineCacheAdapter->fetch($request->getFingerprint());
    }

    /**
     * {@inheritdoc}
     */
    public function save(ResponseInterface $response, RequestInterface $request, $lifetime = null)
    {
        $this->doctrineCacheAdapter->save($request->getFingerprint(), $response, $lifetime);
        return $this;
    }
}