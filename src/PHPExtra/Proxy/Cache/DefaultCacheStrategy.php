<?php

namespace PHPExtra\Proxy\Cache;

use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\ResponseInterface;

/**
 * The DefaultCacheStrategy class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class DefaultCacheStrategy implements CacheStrategyInterface
{
    /**
     * {@inheritdoc}
     */
    public function canUseResponseFromCache(RequestInterface $request, ResponseInterface $cachedResponse = null)
    {
        if($cachedResponse !== null && $request->getMethod() == 'GET' && !$request->isNoCache()) {
            return $this->isResponseFreshEnoughForTheClient($request, $cachedResponse);
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function canStoreResponseInCache(ResponseInterface $response, RequestInterface $request)
    {
        return $this->isResponseFromCache($response);
    }

    /**
     * Tell if given response with its max age is fresh enough for the client
     * that can also request a response of some age
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     *
     * @return bool
     */
    protected function isResponseFreshEnoughForTheClient(RequestInterface $request, ResponseInterface $response)
    {

        $compare = array();
        if($request->getMaxAge() !== null){
            $compare[] = $request->getMaxAge();
        }

        if($response->getMaxAge() !== null){
            $compare[] = $response->getMaxAge();
        }

        if(count($compare) == 0 || ($maxAge = min($compare)) <= 0){
            return false;
        }

        $expirationDate = $response->getDate()->add(new \DateInterval(sprintf('PT%sS', $maxAge)));
        $now = new \DateTime('now', $response->getDate()->getTimezone());

        return $expirationDate >= $now;
    }

    /**
     * Tell if current response comes from cache
     *
     * @param ResponseInterface $response
     *
     * @return bool
     */
    private function isResponseFromCache(ResponseInterface $response)
    {
        return $response->hasHeaderWithValue('X-Cache', 'HIT');
    }
}