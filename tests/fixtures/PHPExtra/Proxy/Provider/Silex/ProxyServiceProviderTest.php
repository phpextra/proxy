<?php


/**
 * The ProxyServiceProviderTest class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class ProxyServiceProviderTest extends PHPUnit_Framework_TestCase 
{
    public function testCreateSilexApplication()
    {
        $logger = new \Psr\Log\NullLogger();

        $silex = new Silex\Application(array(
            'debug' => true,
            'logger' => $logger
        ));

        $silex->register(new \PHPExtra\Proxy\Provider\Silex\ProxyServiceProvider(), array(
            'logger' => $logger,
            'proxy.storage' => new \PHPExtra\Proxy\Storage\InMemoryStorage(),
            'proxy.adapter.name' => 'dummy',
            'proxy.adapter.dummy.handler' => $silex->protect(function(\PHPExtra\Proxy\Http\RequestInterface $request){
                return new \PHPExtra\Proxy\Http\Response('I see ' . $request->getRequestUri());
            }),
            'proxy.logger' => $logger,
        ));

        $silex->register(new \PHPExtra\EventManager\Silex\EventManagerServiceProvider());
        $silex->boot();

        $request = \Symfony\Component\HttpFoundation\Request::create('http://test.com/ping');

        $response = $silex->handle($request);
        $silex->terminate($request, $response);

        $this->assertEquals('I see /ping', $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
    }
}
 