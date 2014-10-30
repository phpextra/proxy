<?php

use PHPExtra\Proxy\Engine\Dummy\DummyEngine;
use PHPExtra\Proxy\ProxyInterface;

/**
 * The ProxyTest class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class ProxyTest extends PHPUnit_Framework_TestCase 
{
    /**
     * @var ProxyInterface
     */
    private $proxy;

    /**
     * @var DummyEngine
     */
    private $engine;

    protected function setUp()
    {
        $logger = new \Psr\Log\NullLogger();

        $em = new \PHPExtra\EventManager\EventManager();
        $em->setLogger($logger);

        $engine  = new DummyEngine();
        $engine->setLogger($logger);
        $engine->setHandler(function(\PHPExtra\Proxy\Http\RequestInterface $request){
            return new \PHPExtra\Proxy\Http\Response('OK');
        });
        $this->engine = $engine;

        $proxy = new \PHPExtra\Proxy\Proxy();
        $proxy->setLogger($logger);
        $proxy->setEngine($engine);
        $proxy->setEventManager($em);

        $this->proxy = $proxy;
    }

    public function testProxyIsAbleToHandleBasicRequest()
    {
        $request = \PHPExtra\Proxy\Http\Request::create('/index.html');
        $response = $this->proxy->handle($request);

        $this->assertEquals(200, $response->getStatusCode());
    }




}
 