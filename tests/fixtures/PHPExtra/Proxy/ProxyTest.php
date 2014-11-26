<?php
use PHPExtra\Proxy\Http\RequestInterface;

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

    public function testProxyDoesNotCacheResponseIfItIsServerError()
    {
        $request1 = \PHPExtra\Proxy\Http\Request::create('http://onet.pl/index.html');
        $request1->addHeader('Max-Age', 60000);

        $request2 = clone $request1;

        $response = new \PHPExtra\Proxy\Http\Response('Server error', 503);
        $response->setPublic();
        $response->setSharedMaxAge(60000);

        $this->adapter->setHandler(function(RequestInterface $request) use($response) {
            return $response;
        });

        $this->proxy->handle($request1);
        $response = $this->proxy->handle($request2);

        $this->assertTrue($response->hasHeaderWithValue('X-Cache', 'MISS'));
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

    public function testCachedProxyResponseHasAgeHeader()
    {
        $request = \PHPExtra\Proxy\Http\Request::create('http://example.com/index.html');
        $response = new \PHPExtra\Proxy\Http\Response('OK', 200);
        $response->setDate(new DateTime('-1 day'));
        $response->setPublic();
        $response->setSharedMaxAge(200000);

        $this->storage->save($request, $response);

        $handledResponse = $this->proxy->handle($request);

        $this->assertTrue($handledResponse->hasHeader('Age'), 'No Age header on response');
        $ageHeaders = $handledResponse->getHeader('Age');

        // todo mock time somehow, or work around
        $this->assertGreaterThanOrEqual(86400, $ageHeaders[0], 'Age was too small.');
        $this->assertGreaterThan(0, $ageHeaders[0]);
    }

    public function testProxyDisplaysWelcomePageIfRequestedHostIsOnProxyHostsList()
    {
        $this->proxy->setConfig(new \PHPExtra\Proxy\Config(array(
            'hosts' => array(
                array('localhost', 80),
                array('127.0.0.1', 80),
                array('proxy.local', 9999),
            )
        )));

        $request = \PHPExtra\Proxy\Http\Request::create('http://localhost/index.html');
        $response = $this->proxy->handle($request);

        $this->assertEquals($this->getResource('home.html'), $response->getBody());
        $this->assertEquals(200, $response->getStatusCode());

        $request = \PHPExtra\Proxy\Http\Request::create('http://proxy.local:9999');
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
 