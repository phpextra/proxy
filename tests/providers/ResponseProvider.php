<?php
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;

return array(
        array(
            new Response(200, array
                (
                    'cache-control' => 'private',
                    'via' => array('proxy1', 'proxy2', 'proxy3'),
                    'set-cookie' => 'cookie1=abc; expires=Fri, 21-Nov-2014 13:40:43 GMT; path=/',
                    'date' => 'Thu, 20 Nov 2014 13:40:45 GMT',
                ),
                Stream::factory('"ok"')
            )
        ),
        array(
            new Response(200, array
                (
                    'cache-control' => 'private',
                    'set-cookie' => array('cookie1=abc; expires=Fri, 21-Nov-2014 13:40:43 GMT; path=/', 'cookie2=cab; expires=Fri, 21-Nov-2014 13:40:43 GMT; path=/'),
                    'date' => 'Thu, 20 Nov 2014 13:40:45 GMT',
                ),
                Stream::factory('"ok"')
            )
        ),
        array(
            new Response(200, array(
                    'cache-control' => 'private',
                    'content-type' => 'application/json; charset=utf-8',
                    'x-server-nr' => 'W2',
                    'date' => 'Thu, 20 Nov 2014 13:40:45 GMT',
                    'via' => '1.1 proxy.example.com:80 (squid)',
                    'connection' => 'keep-alive'
                ),
                Stream::factory('"ok"')
            )),
    );