<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

use PHPExtra\Proxy\Adapter\Dummy\DummyAdapter;
use PHPExtra\Proxy\Firewall\FirewallInterface;
use PHPExtra\Proxy\Logger\LoggerProxy;
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

    /**
     * @var FirewallInterface
     */
    protected $firewall;

    /**
     * @var \Monolog\Handler\TestHandler
     */
    protected $logHandler;

    protected function setUp()
    {
        $this->logHandler = new \Monolog\Handler\TestHandler();
        $monolog = new Monolog\Logger('test', array($this->logHandler));

        $logger = new LoggerProxy($monolog);
        $storage = new InMemoryStorage();

        $adapter  = new DummyAdapter();
        $adapter->setLogger($logger);
        $adapter->setHandler(function(\PHPExtra\Proxy\Http\RequestInterface $request){
            return new \PHPExtra\Proxy\Http\Response('OK');
        });

        $factory = \PHPExtra\Proxy\ProxyFactory::getInstance();
        $factory
            ->setLogger($logger)
            ->setStorage($storage)
            ->setAdapter($adapter)
        ;

        $factory->getEventManager()->setThrowExceptions(true);

        $proxy = $factory->create();
        $proxy->setDebug(false);

        $this->firewall = $factory->getFirewall();
        $this->storage = $factory->getStorage();
        $this->adapter = $factory->getAdapter();
        $this->proxy = $proxy;
    }
} 