<?php

/**
 * Copyright (c) 2014 Paweł Łuczkiewicz <me@agares.info>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Storage;

use Doctrine\Common\Cache\Cache;
use Doctrine\Common\Cache\MemcachedCache;

/**
 * Memcache storage implementation
 *
 * @author Paweł Łuczkiewicz <me@agares.info>
 */
class MemcachedStorage extends DoctrineCacheStorage
{
    /**
     * @var Cache
     */
    private $driver;

    /**
     * @param array  $servers Array of servers, where each server entry is in format array(SERVER_IP, SERVER_PORT, SERVER_WEIGHT), where SERVER_WEIGHT is optional (the bigger value, the bigger there is chance to be connected to that server).
     * @param string $prefix  Prefix for all the keys stored in memcached using this adapter.
     */
    public function __construct(array $servers, $prefix)
    {
        $driver = new MemcachedCache();
        $memcached = new \Memcached();
        $memcached->addServers($servers);
        $memcached->setOption(\Memcached::OPT_PREFIX_KEY, $prefix);
        $driver->setMemcached($memcached);
        $this->driver = $driver;

        parent::__construct($driver);
    }

    /**
     * Clear all the entries stored by this storage.
     */
    public function clear()
    {
        $this->driver->deleteAll();
    }
} 