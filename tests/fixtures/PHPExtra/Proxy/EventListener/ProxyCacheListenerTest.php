<?php

use PHPExtra\Proxy\Event\ProxyRequestEvent;
use PHPExtra\Proxy\EventListener\ProxyCacheListener;
use PHPExtra\Proxy\Http\Request;
use PHPExtra\Proxy\Http\Response;
use PHPExtra\Proxy\Logger\LoggerProxy;
use PHPExtra\Proxy\Storage\InMemoryStorage;
use PHPExtra\Proxy\Storage\StorageInterface;

class ProxyCacheListenerTest extends ProxyTestCase
{
    /**
     * @var CacheStrategyMock
     */
    private $cacheStrategy;

    /**
     * @var ProxyCacheListener
     */
    private $proxyCacheListener;

    public function setUp()
    {
        parent::setUp();

        $this->cacheStrategy = new CacheStrategyMock();
        $this->proxyCacheListener = new ProxyCacheListener($this->cacheStrategy, $this->storage);
    }

    public function testResponseAndHitHeaderAreSetWhenResponseCanBeReadFromCache()
    {
        $this->cacheStrategy->setCanUseResponseFromCache(true);

        $request = Request::create('/');
        $response = new Response('"ok"');

        $this->storage->save($request, $response);

        $event = new ProxyRequestEvent($request, null, $this->proxy);
        $event->setLogger(new LoggerProxy());
        $this->proxyCacheListener->onProxyRequest($event);

        $this->assertNotNull($event->getResponse());
        $this->assertEquals($response, $event->getResponse());
        $this->assertTrue($response->hasHeaderWithValue('X-Cache', 'HIT'));
    }

    public function testResponseIsStoredInCacheWhenItIsAllowedByStrategy()
    {
        $this->cacheStrategy->setCanStoreResponseInCache(true);

        $request = Request::create('/');
        $response = new Response('"ok"');

        $event = new \PHPExtra\Proxy\Event\ProxyResponseEvent($request, $response, $this->proxy);
        $event->setLogger(new LoggerProxy());

        $this->proxyCacheListener->onProxyResponse($event);

        $storedResponse = $this->storage->fetch($request);

        $this->assertNotNull($storedResponse);
        $this->assertEquals($response, $storedResponse);
    }
} 