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
 * The DoctrineCacheStorage class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class DoctrineCacheStorage implements StorageInterface
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
        $result = $this->doctrineCacheAdapter->fetch($request->getFingerprint());
        return $result instanceof ResponseInterface ? $result : null;
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
    public function save(RequestInterface $request, ResponseInterface $response, $lifetime = null)
    {
        if($lifetime === null) {
            $expireDate = $response->getExpireDate();
            if($expireDate !== null) {
                $now = new \DateTime();
                $lifetime = $now->getTimestamp() - $expireDate->getTimestamp();
            }
        }

        $this->doctrineCacheAdapter->save($request->getFingerprint(), $response, $lifetime);
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(RequestInterface $request)
    {
        $this->doctrineCacheAdapter->delete($request->getFingerprint());

        return $this;
    }
}