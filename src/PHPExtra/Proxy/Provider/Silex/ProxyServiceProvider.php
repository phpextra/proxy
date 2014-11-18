<?php

namespace PHPExtra\Proxy\Provider\Silex;

use PHPExtra\Proxy\Adapter\Dummy\DummyAdapter;
use PHPExtra\Proxy\Adapter\Guzzle4\Guzzle4Adapter;
use PHPExtra\Proxy\Cache\DefaultCacheManager;
use PHPExtra\Proxy\EventListener\DefaultProxyListener;
use PHPExtra\Proxy\EventListener\ProxyCacheListener;
use PHPExtra\Proxy\Firewall\DefaultFirewall;
use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\Response;
use PHPExtra\Proxy\Logger\LoggerProxy;
use GuzzleHttp\Client;
use Monolog\Logger;
use PHPExtra\Proxy\ProxyFactory;
use PHPExtra\Proxy\Storage\FilesystemStorage;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Connect proxy to a silex application
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class ProxyServiceProvider implements ServiceProviderInterface, ControllerProviderInterface
{
    /**
     * {@inheritdoc}
     */
    public function register(Application $app)
    {
        /**
         * Available options
         */

        $app['proxy.debug'] = $app['debug'];

        $app['guzzle.base_url'] = '';
        $app['guzzle.timeout'] = 30;
        $app['guzzle.connect_timeout'] = 10;
        $app['guzzle.allow_redirects'] = false;
        $app['guzzle.exceptions'] = false;
        $app['guzzle.verify'] = false;
        $app['guzzle.debug'] = false;
        $app['guzzle.decode_content'] = true;

        $app['proxy.controller.prefix'] = '';
        $app['proxy.logger.name'] = 'Proxy';
        $app['proxy.logger.level'] = Logger::INFO;
        $app['proxy.adapter.name'] = 'guzzle4';
        $app['proxy.storage.filesystem.directory'] = sys_get_temp_dir();

        /**
         * Services
         */

        $app['proxy'] = $app->share(
            function (Application $app) {

                $proxy = ProxyFactory::getInstance()
                    ->setLogger($app['proxy.logger'])
                    ->setAdapter($app['proxy.adapter'])
                    ->setEventManager($app['event_manager'])
                    ->setFirewall($app['proxy.firewall'])
                    ->create()
                ;

                $proxy->setDebug($app['proxy.debug']);

                foreach($app['proxy.listeners'] as $listener){
                    $app['event_manager']->addListener($listener);
                }

                return $proxy;
            }
        );

//        $app['proxy.logger.monolog'] = $app->share(function() use ($app){
//            $logger = new Logger($app['proxy.logger.name']);
//            $logger->pushHandler($app['proxy.logger.handler']);
//            $logger->pushHandler($app['monolog.handler']);
//
//            $logger->pushProcessor(new ProcessIdProcessor());
//
//            $logger->pushProcessor(function(array $record) use ($app){
//                    $request = $app['request'];
//                    /** @var RequestInterface $request */
//
//                    $record['extra']['client_ip'] = $request ? $request->getClientIp() : null;
//                    return $record;
//                });
//
//            return $logger;
//        });

        $app['proxy.logger'] = $app->share(function(Application $app){
            return new LoggerProxy($app['logger']);
        });

        $app['proxy.firewall'] = $app->share(function() use ($app){
            return new DefaultFirewall();
        });

//        $app['proxy.logger.handler'] = $app->share(function(Application $app){
//            return new StreamHandler($app['proxy.logger.logfile'], $app['proxy.logger.level']);
//        });

        $app['proxy.adapter.dummy'] = $app->share(
            function (Application $app) {
                $adapter = new DummyAdapter($app['proxy.logger']);
                $adapter->setHandler($app['proxy.adapter.dummy.handler']);
                return $adapter;
            }
        );

        $app['proxy.adapter.dummy.handler'] = $app->protect(function(){
            return new Response('Proxy works !', 200);
        });

        $app['proxy.adapter.guzzle4'] = $app->share(
            function (Application $app) {
                return new Guzzle4Adapter($app['guzzle.client']);
            }
        );

        $app['proxy.adapter'] = $app->share(function(Application $app){
            return $app['proxy.adapter.' . $app['proxy.adapter.name']];
        });

        $app['proxy.storage'] = $app->share(function(Application $app){
            $stack = new FilesystemStorage($app['proxy.storage.filesystem.directory']);
            return $stack;
        });

        $app['proxy.cache_manager'] = $app->share(function(Application $app){
            $stack = new DefaultCacheManager($app['proxy.storage']);
            return $stack;
        });

        $app['proxy.listeners'] = $app->share(function(Application $app){
            return array(
                new DefaultProxyListener($app['proxy.firewall']),
                new ProxyCacheListener($app['proxy.cache_manager']),
            );
        });

        $app['proxy.controller'] = $app->protect(function(Request $request, Application $app){

            if(!$request instanceof RequestInterface){
                $request = new \PHPExtra\Proxy\SymfonyBridge\Request($request);
            }

            $response = $app['proxy']->handle($request);

            if(!$response instanceof \Symfony\Component\HttpFoundation\Response){
                $response = new \PHPExtra\Proxy\SymfonyBridge\Response($response);
            }

            return $response;
        });

        $app['guzzle.client'] = $app->share(
            function () use ($app) {
                $client = new Client(
                    array(
                        'base_url'          => $app['guzzle.base_url'],
                        'timeout'           => $app['guzzle.timeout'],          // waiting for response timeout
                        'connect_timeout'   => $app['guzzle.connect_timeout'],   // connecting to server timeout
                        'allow_redirects'   => $app['guzzle.allow_redirects'],
                        'exceptions'        => $app['guzzle.exceptions'],
                        'verify'            => $app['guzzle.verify'],
                        'debug'             => $app['guzzle.debug'],
                        'decode_content'    => $app['guzzle.decode_content'],
                    )
                );

                return $client;
            }
        );
    }

    /**
     * {@inheritdoc}
     */
    public function connect(Application $app)
    {
        /** @var ControllerCollection $controllers */
        $controllers = $app['controllers_factory'];
        $controllers->match('{any}', $app['proxy.controller'])->assert('any', '(?!(_profiler)).*');

        return $controllers;
    }
    
    /**
     * {@inheritdoc}
     */
    public function boot(Application $app)
    {
        $app->mount($app['proxy.controller.prefix'], $this);
    }
}