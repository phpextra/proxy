<?php

namespace PHPExtra\Proxy\Storage;

use Doctrine\Common\Cache\MemcachedCache;

class MemcacheStorage extends DoctrineCacheStorage
{
    public function __construct(array $servers)
    {
        $driver = new MemcachedCache();
        $memcached = new \Memcached();
        $memcached->addServers($servers);
        $driver->setMemcached($memcached);

        parent::__construct($driver);
    }
} 