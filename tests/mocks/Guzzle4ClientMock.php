<?php

use GuzzleHttp\Message\RequestInterface;
use GuzzleHttp\Stream\Stream;
use PHPExtra\Proxy\Http\ResponseInterface;

class Guzzle4ClientMock extends \GuzzleHttp\Client
{
    /**
     * @var Closure
     */
    private $callback;

    function __construct($callback = null)
    {
        if($callback = null) {
            /**
             * @return \GuzzleHttp\Message\Response
             */
            $callback = function() {
                return new \GuzzleHttp\Message\Response(200, array(), Stream::factory('ok'));
            };
        }
        $this->callback = $callback;

        parent::__construct();
    }

    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function send(RequestInterface $request)
    {
        return call_user_func($this->callback, $request);
    }

    /**
     * @param Closure $callback
     */
    public function setCallback($callback)
    {
        $this->callback = $callback;
    }

    public static function okResponse()
    {
        return new \GuzzleHttp\Message\Response(200, array(), Stream::factory('ok'));
    }
}