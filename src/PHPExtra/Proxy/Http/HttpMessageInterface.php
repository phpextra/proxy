<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Http;

/**
 * The HttpMessageInterface interface
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface HttpMessageInterface
{
    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function addHeader($name, $value);

    /**
     * @param string $name
     * @param array|string  $value
     *
     * @return $this
     */
    public function setHeader($name, $value);

    /**
     * @param array $headers
     *
     * @return $this
     */
    public function setHeaders(array $headers);

    /**
     * @param string $name
     *
     * @param string $default
     *
     * @return array
     */
    public function getHeader($name, $default = null);

    /**
     * @param string $name
     *
     * @return $this
     */
    public function removeHeader($name);

    /**
     * Get all headers
     *
     * @return array[]
     */
    public function getHeaders();

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasHeader($name);

    /**
     * @param string $name
     * @param string $value
     *
     * @return bool
     */
    public function hasHeaderWithValue($name, $value);
} 