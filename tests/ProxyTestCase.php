<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */
use PHPExtra\Proxy\Adapter\Dummy\DummyAdapter;
use PHPExtra\Proxy\ProxyInterface;
use PHPExtra\Proxy\Storage\InMemoryStorage;


/**
 * The ProxyTestCase class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class ProxyTestCase extends PHPUnit_Framework_TestCase
{
    /**
     * @var ProxyInterface
     */
    protected $proxy;

    /**
     * @var DummyAdapter
     */
    protected $adapter;

    /**
     * @var InMemoryStorage
     */
    protected $storage;

    protected function setUp()
    {
        $logger = new \Psr\Log\NullLogger();

        $em = new \PHPExtra\EventManager\EventManager();
        $em->setThrowExceptions(true);
        $em->setLogger($logger);

        $storage = new InMemoryStorage();

        $adapter  = new DummyAdapter();
        $adapter->setLogger($logger);
        $adapter->setHandler(function(\PHPExtra\Proxy\Http\RequestInterface $request){
                return new \PHPExtra\Proxy\Http\Response('OK');
            });

        $proxy = new \PHPExtra\Proxy\Proxy();
        $proxy->setLogger($logger);
        $proxy->setAdapter($adapter);
        $proxy->setEventManager($em);
        $proxy->setCacheManager(new \PHPExtra\Proxy\Cache\DefaultCacheManager($storage));

        $this->storage = $storage;
        $this->adapter = $adapter;
        $this->proxy = $proxy;
    }
} 