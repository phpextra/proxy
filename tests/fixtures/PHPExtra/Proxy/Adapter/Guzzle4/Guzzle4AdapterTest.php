<?php

use GuzzleHttp\Client;
use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;
use GuzzleHttp\Subscriber\Mock;
use PHPExtra\Proxy\Adapter\Guzzle4\Guzzle4Adapter;
use PHPExtra\Proxy\Http\Request;

class Guzzle4AdapterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Guzzle4ClientMock
     */
    private $client;

    /**
     * @var Guzzle4Adapter
     */
    private $adapter;

    protected function setUp()
    {
        $this->client = new Guzzle4ClientMock();
        $this->adapter = new Guzzle4Adapter($this->client);
    }

    public function testAdapterConvertsProxyRequestToGuzzleRequest()
    {
        $convertedRequest = null;

        $this->client->setCallback(function(\GuzzleHttp\Message\RequestInterface $request) use (&$convertedRequest) {
            $convertedRequest = $request;

            return Guzzle4ClientMock::okResponse();
        });

        $this->adapter->handle(Request::create('http://example.com/test', 'GET'));

        $this->assertInstanceOf('GuzzleHttp\Message\RequestInterface', $convertedRequest);
    }

    public function testRequestConverterPassesCookies()
    {
        $this->markTestSkipped('Doesn\'t work, needs further investigation.');

        /**
         * @var RequestInterface $convertedRequest
         */
        $convertedRequest = null;

        $this->client->setCallback(function(\GuzzleHttp\Message\RequestInterface $request) use (&$convertedRequest) {
            $convertedRequest = $request;

            return Guzzle4ClientMock::okResponse();
        });

        $this->adapter->handle(Request::create('http://example.com/test', 'GET', array(), array('test' => 'wowsotested')));

        $cookieHeader = $convertedRequest->getHeader('Cookie');

        $this->assertFalse(strpos($cookieHeader, 'test=wowsotested') === false);
    }

    public function testRequestConverterPassesCustomHeaders()
    {
        /** @var RequestInterface $convertedRequest */
        $convertedRequest = null;

        $this->client->setCallback(function (RequestInterface $request) use(&$convertedRequest) {
            $convertedRequest = $request;

            return Guzzle4ClientMock::okResponse();
        });

        $this->adapter->handle(Request::create('/', 'POST', array(), array(), array(), array('HTTP_XMobileAppVersion' => 'test123')));

        $this->assertTrue($convertedRequest->hasHeader('XMobileAppVersion'));
        $this->assertEquals('test123', $convertedRequest->getHeader('XMobileAppVersion'));
    }

    /**
     * @dataProvider responseProvider
     *
     * @param \GuzzleHttp\Message\ResponseInterface $response
     */
    public function testAdapterConvertsGuzzleResponseToSymfonyResponse(\GuzzleHttp\Message\ResponseInterface $response)
    {
        $this->client->setCallback(function(RequestInterface $request) use ($response) {
            return $response;
        });

        $convertedResponse = $this->adapter->handle(Request::create('/'));

        $this->assertEquals($response->getHeaders(), $convertedResponse->getHeaders());
        $this->assertEquals($response->getStatusCode(), $convertedResponse->getStatusCode());
        $this->assertEquals($response->getBody(), $convertedResponse->getBody());
    }

    public function responseProvider()
    {
        return include __DIR__.'/../../../../../providers/ResponseProvider.php';
    }
} 