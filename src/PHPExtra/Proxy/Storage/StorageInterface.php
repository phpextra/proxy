<?php

/**
 * Copyright (c) 2013 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Storage;

use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\ResponseInterface;

/**
 * The StorageAdapterInterface interface
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface StorageInterface
{
    /**
     * @param RequestInterface $request
     *
     * @return ResponseInterface
     */
    public function fetch(RequestInterface $request);

    /**
     * @param RequestInterface $request
     *
     * @return bool
     */
    public function has(RequestInterface $request);

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param int               $lifetime
     *
     * @return $this
     */
    public function save(RequestInterface $request, ResponseInterface $response, $lifetime = null);

    /**
     * @param RequestInterface $request
     *
     * @return $this
     */
    public function delete(RequestInterface $request);
}