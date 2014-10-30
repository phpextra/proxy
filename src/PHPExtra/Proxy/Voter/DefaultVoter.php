<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Voter;

use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\ResponseInterface;

/**
 * The DefaultVoter class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class DefaultVoter extends AbstractVoter
{
    /**
     * {@inheritdoc}
     */
    public function canUseResponseFromStorage(ResponseInterface $response, RequestInterface $request)
    {
        return !$request->isNoCache();
    }

    /**
     * {@inheritdoc}
     */
    public function canStoreResponseInStorage(ResponseInterface $response, RequestInterface $request)
    {
        return $response->isOk() && !$response->isEmpty() && $response->isCacheable();
    }
}