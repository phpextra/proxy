<?php

namespace PHPExtra\Proxy\Provider\Silex;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use PHPExtra\Proxy\Adapter\Dummy\DummyAdapter;
use PHPExtra\Proxy\Adapter\Guzzle4\Guzzle4Adapter;
use PHPExtra\Proxy\Cache\DefaultCacheStrategy;
use PHPExtra\Proxy\Config;
use PHPExtra\Proxy\EventListener\DefaultProxyListener;
use PHPExtra\Proxy\EventListener\ProxyCacheListener;
use PHPExtra\Proxy\Firewall\DefaultFirewall;
use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\Response;
use GuzzleHttp\Client;
use Monolog\Logger;
use PHPExtra\Proxy\Provider\Silex\Listener\AccessLoggerListener;
use PHPExtra\Proxy\Proxy;
use PHPExtra\Proxy\Storage\FilesystemStorage;
use Psr\Log\LogLevel;
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

        $app['guzzle.base_url'] = '';
        $app['guzzle.timeout'] = 30;
        $app['guzzle.connect_timeout'] = 10;
        $app['guzzle.allow_redirects'] = false;
        $app['guzzle.exceptions'] = false;
        $app['guzzle.verify'] = false;
        $app['guzzle.debug'] = false;
        $app['guzzle.decode_content'] = true;

        $app['proxy.name'] = 'PHPExtraProxy';
        $app['proxy.version'] = '1.0.0';
        $app['proxy.secret'] = md5(__FILE__);
        $app['proxy.hosts'] = array(
            array('localhost', 80),
            array('localhost', 443),
            array('127.0.0.1', 80),
            array('127.0.0.1', 443),
            array('::1', 80),
            array('::1', 443),
        );

        $app['proxy.controller.prefix'] = '';
        $app['proxy.logger.access_log.logfile'] = function() use ($app){
            return $app['monolog.logfile'];
        };

        $app['proxy.adapter.name'] = 'guzzle4';
        $app['proxy.storage.filesystem.directory'] = sys_get_temp_dir();

        $app['proxy.firewall.allowed_domains'] = array();
        $app['proxy.firewall.allowed_clients'] = array();

        /**
         * Services and configuration
         */

        $app['proxy.config'] = $app->share(function() use ($app){
            $config = new Config(array(
                'name'      => $app['proxy.name'],
                'version'   => $app['proxy.version'],
                'secret'    => $app['proxy.secret'],
                'hosts'     => $app['proxy.hosts'],
            ));
            return $config;
        });

        $app['proxy'] = $app->share(
            function (Application $app) {

                $proxy = new Proxy($app['debug']);

                $proxy
                    ->setConfig($app['proxy.config'])
                    ->setLogger($app['logger'])
                    ->setAdapter($app['proxy.adapter'])
                    ->setEventManager($app['event_manager'])
                    ->setFirewall($app['proxy.firewall'])
                ;

                return $proxy;
            }
        );

        $app['proxy.firewall'] = $app->share(function() use ($app){
            $firewall = new DefaultFirewall();
            foreach($app['proxy.firewall.allowed_clients'] as $clientIp){
                $firewall->allowIp($clientIp);
            }

            foreach($app['proxy.firewall.allowed_domains'] as $domain){
                $firewall->allowDomain($domain);
            }

            return $firewall;
        });

        $app['proxy.logger.factory'] = $app->protect(function($name) use ($app){
            $handlers = isset($app['proxy.logger.' . $name . '.handlers']) ? $app['proxy.logger.' . $name . '.handlers'] : array($app['monolog.handler']);
            $processors = isset($app['proxy.logger.' . $name . '.processors']) ? $app['proxy.logger.' . $name . '.processors'] : array();
            return new Logger($name, $handlers, $processors);
        });

        $app['proxy.logger.access_log'] = $app->share(function() use ($app){
            return $app['proxy.logger.factory']('access_log');
        });

        $app['proxy.logger.access_log.formatter'] = $app->share(function(){
            $output = "%level_name% %datetime% %message%\n";
            return new LineFormatter($output);
        });

        $app['proxy.logger.access_log.handlers'] = $app->share(function() use ($app){
            $handler = new StreamHandler($app['proxy.logger.access_log.logfile'], LogLevel::INFO);
            $handler->setFormatter($app['proxy.logger.access_log.formatter']);
            return array($handler);
        });

        $app['proxy.adapter'] = $app->share(function(Application $app){
            return $app['proxy.adapter.' . $app['proxy.adapter.name']];
        });

        $app['proxy.adapter.dummy'] = $app->share(
            function (Application $app) {
                $adapter = new DummyAdapter();
                $adapter->setHandler($app['proxy.adapter.dummy.handler']);
                return $adapter;
            }
        );

        $app['proxy.adapter.dummy.handler'] = $app->protect(function(){
            return new Response('Proxy works !', 200);
        });

        $app['proxy.adapter.guzzle4'] = $app->share(
            function (Application $app) {
                $adapter = new Guzzle4Adapter($app['guzzle.client']);
                $adapter->setLogger($app['proxy.logger.access_log']);
                return $adapter;
            }
        );

        $app['proxy.storage'] = $app->share(function(Application $app){
            $stack = new FilesystemStorage($app['proxy.storage.filesystem.directory']);
            return $stack;
        });

        $app['proxy.cache_strategy'] = $app->share(function(Application $app){
            $stack = new DefaultCacheStrategy();
            return $stack;
        });

        $app['proxy.listeners'] = $app->share(function(Application $app){
            return array(
                new DefaultProxyListener($app['proxy.firewall']),
                new ProxyCacheListener($app['proxy.cache_strategy'], $app['proxy.storage']),
                new AccessLoggerListener($app['proxy.logger.access_log'], $app['stopwatch']),
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
        foreach($app['proxy.listeners'] as $listener){
            $app['event_manager']->addListener($listener);
        }

        if($app['debug'] == true){
            $app->error(function(\Exception $e, $code){
                throw $e;
            });
        }

        $app->mount($app['proxy.controller.prefix'], $this);
    }
}