<?php

/**
 * The ProxyTest class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class ProxyTest extends ProxyTestCase
{
    public function testProxyIsAbleToHandleBasicRequest()
    {
        $request = \PHPExtra\Proxy\Http\Request::create('http://example.com/index.html');
        $response = $this->proxy->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProxyReturnsCachedResponseWhenResponseIsFresh()
    {
        $request = \PHPExtra\Proxy\Http\Request::create('http://example.com/index.html');
        $response = new \PHPExtra\Proxy\Http\Response('Cached response', 200);
        $response->setMaxAge(100);

        $this->storage->save($request, $response);
        $response = $this->proxy->handle($request);

        $this->assertEquals('Cached response', $response->getBody());
    }

    public function testProxyDoesNotReturnCachedResponseWhenResponseIsNotFresh()
    {
        $request = \PHPExtra\Proxy\Http\Request::create('http://example.com/index.html');
        $response = new \PHPExtra\Proxy\Http\Response('Cached response', 200);
        $response->setMaxAge(0);

        $this->storage->save($request, $response);
        $response = $this->proxy->handle($request);

        $this->assertEquals('OK', $response->getBody());
    }

    public function testProxyReturnsFreshResponseForNoCacheRequest()
    {
        $request = \PHPExtra\Proxy\Http\Request::create('http://example.com/index.html');
        $request->addHeader('Cache-Control', 'no-cache');
        $this->storage->save($request, new \PHPExtra\Proxy\Http\Response('Fake response', 500));

        $response = $this->proxy->handle($request);
        $this->assertEquals('OK', $response->getBody());
    }

    public function testProxyReturnsFreshResponseForMaxAgeZeroRequest()
    {
        $request = \PHPExtra\Proxy\Http\Request::create('http://example.com/index.html');
        $request->addHeader('Max-Age', '0');
        $this->storage->save($request, new \PHPExtra\Proxy\Http\Response('Fake response', 500));

        $response = $this->proxy->handle($request);
        $this->assertEquals('OK', $response->getBody());
    }

    public function testProxyReturnsFreshResponseForNoStoreRequest()
    {
        $request = \PHPExtra\Proxy\Http\Request::create('http://example.com/index.html');
        $request->addHeader('Cache-Control', 'no-store');
        $this->storage->save($request, new \PHPExtra\Proxy\Http\Response('Fake response', 500));

        $response = $this->proxy->handle($request);

        $this->assertEquals('OK', $response->getBody());
    }

    public function testProxyReturnsFreshResponseIfClientMaxAgeIsLessThanResponseAge()
    {
        $request = \PHPExtra\Proxy\Http\Request::create('http://example.com/index.html');
        $request->addHeader('Max-Age', 60);

        $response = new \PHPExtra\Proxy\Http\Response('Cached response', 200);
        $newDate = $response->getDate()->sub(new \DateInterval('PT120S'));
        $response->setDate($newDate); // response returns always NOW if no date was set @todo fixme

        $this->storage->save($request, $response);

        $response = $this->proxy->handle($request);
        $this->assertEquals('OK', $response->getBody());
    }

    public function testProxyReturnsCachedResponseIfClientMaxAgeIsGreaterThanResponseAge()
    {
        $request = \PHPExtra\Proxy\Http\Request::create('http://example.com/index.html');
        $request->addHeader('Max-Age', 3600);

        $response = new \PHPExtra\Proxy\Http\Response('Cached response', 200);
        $response->setHeader('Max-Age', 1800);

        $this->storage->save($request, $response);

        $response = $this->proxy->handle($request);
        $this->assertEquals('Cached response', $response->getBody());
    }

    public function testProxyReturns403ResponseIfRequestedDomainWasNotAllowedByFirewall()
    {
        $request = \PHPExtra\Proxy\Http\Request::create('http://google.cn/index.html');
        $this->firewall->allowDomain('google.com'); // allow anything else
        $response = $this->proxy->handle($request);

        $this->assertEquals($this->getResource('403.html'), $response->getBody());
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testProxyReturns403ResponseIfClientIpWasNotAllowedByFirewall()
    {
        $request = \PHPExtra\Proxy\Http\Request::create('http://example.com/index.html');
        $this->firewall->allowIp('88.88.88.88');
        $response = $this->proxy->handle($request);

        $this->assertEquals($this->getResource('403.html'), $response->getBody());
        $this->assertEquals(403, $response->getStatusCode());
    }

    public function testProxyDisplaysWelcomePageIfRequestedHostIsOnProxyHostsList()
    {
        $request = \PHPExtra\Proxy\Http\Request::create('http://localhost/index.html');
        $this->proxy->setConfig(new \PHPExtra\Proxy\Config(array(
            'hosts' => array(
                array('localhost', 80),
                array('127.0.0.1', 80),
            )
        )));

        $response = $this->proxy->handle($request);
        $this->assertEquals($this->getResource('home.html'), $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testProxyReturns403ResponseForSelfRequest()
    {
        $requestFingerprint = 'e30c1c145e50bf025415f4f2d42d9a40';

        $request = \PHPExtra\Proxy\Http\Request::create('http://localhost/index.html');
        $request->setHeader('PROXY-ID', $requestFingerprint);

        $response = $this->proxy->handle($request);

        $this->assertEquals($this->getResource('403.html'), $response->getBody());
        $this->assertEquals(403, $response->getStatusCode());
    }
}
 