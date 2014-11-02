<?php

use PHPExtra\Proxy\Engine\Dummy\DummyEngine;
use PHPExtra\Proxy\ProxyInterface;

/**
 * The ProxyTest class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class ProxyTest extends ProxyTestCase
{
    public function testProxyIsAbleToHandleBasicRequest()
    {
        $request = \PHPExtra\Proxy\Http\Request::create('/index.html');
        $response = $this->proxy->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProxyReturnsCachedResponse()
    {
        $this->markTestIncomplete('wrong result');

        $request = \PHPExtra\Proxy\Http\Request::create('/index.html');
        $this->storage->save($request, new \PHPExtra\Proxy\Http\Response('Cached response', 200));

        $response = $this->proxy->handle($request);

        $this->assertEquals('Cached response', $response->getBody());
    }

    public function testProxyReturnsFreshResponseForNoCacheRequest()
    {
        $request = \PHPExtra\Proxy\Http\Request::create('/index.html');
        $request->addHeader('Cache-Control', 'no-cache');
        $this->storage->save($request, new \PHPExtra\Proxy\Http\Response('Fake response', 500));

        $response = $this->proxy->handle($request);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProxyReturnsFreshResponseForMaxAgeZeroRequest()
    {
        $this->markTestIncomplete();
    }

    public function testProxyReturnsFreshResponseForNoStoreRequest()
    {
        $this->markTestIncomplete();
    }


}
 