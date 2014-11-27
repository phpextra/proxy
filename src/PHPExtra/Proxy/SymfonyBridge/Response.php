<?php

namespace PHPExtra\Proxy\SymfonyBridge;

use Psr\Http\Message\OutgoingResponseInterface;
use Symfony\Component\HttpFoundation\StreamedResponse as SymfonyStreamedResponse;

/**
 * The Response class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class Response extends SymfonyStreamedResponse
{
    /**
     * @param OutgoingResponseInterface $response
     */
    function __construct(OutgoingResponseInterface $response)
    {
        $callback = function() use ($response){
            return $response->getBody()->getContents();
        };

        parent::__construct($callback, $response->getStatusCode(), $response->getHeaders());
    }
}