<?php

/**
 * Copyright (c) 2013 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Adapter\Guzzle4;

use GuzzleHttp\ClientInterface;
use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\Response;
use PHPExtra\Proxy\Adapter\AbstractProxyAdapter;
use GuzzleHttp\Message\Response as GuzzleResponse;

/**
 * Guzzle4Adapter proxy adapter
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class Guzzle4Adapter extends AbstractProxyAdapter
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @param ClientInterface $client
     */
    function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function handle(RequestInterface $request)
    {
        $guzzleRequest = $this->createGuzzleRequestFromRequest($request);

        // @todo add support for exceptions
        $guzzleResponse = $this->client->send($guzzleRequest);



        /** @var \GuzzleHttp\Message\Response $guzzleResponse */
        $response = $this->createResponseFromGuzzleResponse($guzzleResponse);

        return $response;
    }

    /**
     * @param RequestInterface $request
     *
     * @return \GuzzleHttp\Message\RequestInterface
     */
    protected function createGuzzleRequestFromRequest(RequestInterface $request)
    {
        $headers = array();

        foreach ($request->getHeaders() as $headerName => $headerValue) {
            $headers[$headerName] = $headerValue[0];
        }

        $headers['X-Forwarded-For'] = $request->getClientIp();
        $headers['X-Forwarded-Proto'] = $request->getScheme();

        if(isset($headers['cookie'])){
            unset($headers['cookie']);
        }

        // see http://guzzle.readthedocs.org/en/latest/clients.html#request-options
        $guzzleRequest = $this->client->createRequest(
            $request->getMethod(),
            $request->getUri(),
            array(
                'headers'       => $headers,
                'body'          => $request->getBody(),
                'query'         => $request->getQueryParams(),
                'cookies'       => $request->getCookies(),
                'exceptions'    => false,
                'decode_content' => true,
            )
        );

        return $guzzleRequest;
    }

    /**
     * @param GuzzleResponse $guzzleResponse
     *
     * @return Response
     */
    protected function createResponseFromGuzzleResponse(GuzzleResponse $guzzleResponse)
    {
        $content = null;
        if($guzzleResponse->getBody() !== null){
            $content = $guzzleResponse->getBody()->getContents();
        }

        $response = new Response($content, $guzzleResponse->getStatusCode());
        $response->setHeaders($guzzleResponse->getHeaders());

        $deniedHeaders = array(
            'transfer-encoding',
            'x-powered-by',
            'content-length',
            'content-encoding',
        );

        foreach ($deniedHeaders as $headerName) {
            $response->removeHeader($headerName);
        }

        return $response;
    }
}