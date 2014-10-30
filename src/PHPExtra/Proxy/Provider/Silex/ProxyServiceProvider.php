<?php

namespace PHPExtra\Proxy\Provider\Silex;

use PHPExtra\Proxy\Engine\Dummy\DummyEngine;
use PHPExtra\Proxy\Engine\Guzzle4\Guzzle4Engine;
use PHPExtra\Proxy\EventListener\ProxyCacheListener;
use PHPExtra\Proxy\EventListener\ProxyLoggerListener;
use PHPExtra\Proxy\EventListener\ProxyRequestListener;
use PHPExtra\Proxy\EventListener\ProxyResponseListener;
use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Proxy;
use PHPExtra\Proxy\Storage\FilesystemStorage;
use PHPExtra\Proxy\Voter\DefaultVoter;
use PHPExtra\Proxy\Voter\VoterStack;
use GuzzleHttp\Client;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\ProcessIdProcessor;
use Silex\Application;
use Silex\ControllerCollection;
use Silex\ControllerProviderInterface;
use Silex\ServiceProviderInterface;

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
        $app['proxy.controller.prefix'] = '';
        $app['guzzle.base_url'] = '';
        $app['proxy.logger.name'] = 'Proxy';
        $app['proxy.engine.name'] = 'guzzle4';

        $app['proxy'] = $app->share(
            function (Application $app) {

                $proxy = new Proxy();
                $proxy
                    ->setLogger($app['proxy.logger'])
                    ->setEngine($app['proxy.engine'])
                    ->setEventManager($app['event_manager'])
                ;

                foreach($app['proxy.listeners'] as $listener){
                    $app['event_manager']->addListener($listener);
                }

                return $proxy;
            }
        );

        $app['proxy.logger'] = $app->share(function(Application $app){
            $logger = new Logger($app['proxy.logger.name']);
            $logger->pushHandler($app['monolog.handler']);
            $logger->pushHandler($app['proxy.logger.handler']);

            $logger->pushProcessor(new ProcessIdProcessor());

            $logger->pushProcessor(function(array $record) use ($app){
                $request = $app['request'];
                /** @var RequestInterface $request */

                $record['extra']['client_ip'] = $request ? $request->getClientIp() : null;
                return $record;
            });

            return $logger;
        });

        $app['proxy.logger.handler'] = $app->share(function(Application $app){
            return new StreamHandler($app['proxy.logger.logfile'], $app['proxy.logger.level']);
        });

        $app['proxy.engine.dummy'] = $app->share(
            function (Application $app) {
                return new DummyEngine($app['proxy.logger']);
            }
        );

        $app['proxy.engine.guzzle4'] = $app->share(
            function (Application $app) {
                return new Guzzle4Engine($app['guzzle.client']);
            }
        );

        $app['proxy.engine'] = $app->share(function(Application $app){
            return $app['proxy.engine.' . $app['proxy.engine.name']];
        });

        $app['proxy.storage'] = $app->share(function(Application $app){
            $stack = new FilesystemStorage($app['proxy.storage.filesystem.directory']);
            return $stack;
        });

        $app['proxy.listeners'] = $app->share(function(Application $app){
            return array(
                new ProxyResponseListener(),
                new ProxyCacheListener($app['proxy.storage'], $app['proxy.voter_stack']),
                new ProxyRequestListener(),
                new ProxyLoggerListener(),
            );
        });

        $app['proxy.voter_stack'] = $app->share(function(Application $app){
            $stack = new VoterStack($app['proxy.logger']);
            foreach($app['proxy.voters'] as $voter){
                $stack->addVoter($voter);
            }
            return $stack;
        });

        $app['proxy.voters'] = $app->share(function(){
            return array(
                new DefaultVoter()
            );
        });

        $app['proxy.controller'] = $app->protect(function(Application $app){
            return $app['proxy']->handle($app['request']);
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