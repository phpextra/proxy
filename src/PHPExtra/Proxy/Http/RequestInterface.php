<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Http;

/**
 * The RequestInterface class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface RequestInterface
{
    /**
     * Get request fingerprint
     * Fingerprint should include method, hostname, request uri and query params.
     *
     * @return string
     */
    public function getFingerprint();

    /**
     * @return string
     */
    public function getRequestUri();

    /**
     * @return string
     */
    public function getSchemeAndHttpHost();

    /**
     * @return string
     */
    public function getUri();

    /**
     * @return string
     */
    public function getContentType();

    /**
     * @return string
     */
    public function getQueryString();

    /**
     * @return string
     */
    public function getBasePath();

    /**
     * @return string
     */
    public function getBaseUrl();

    /**
     * @return string
     */
    public function getMethod();

    /**
     * @return string
     */
    public function getScheme();

    /**
     * @return string
     */
    public function getHost();

    /**
     * Returns the HTTP host being requested.
     *
     * @return string
     */
    public function getHttpHost();

    /**
     * @return string
     */
    public function getETags();

    /**
     * @return string
     */
    public function getPort();

    /**
     * @return bool
     */
    public function isNoCache();

    /**
     * @return array
     */
    public function getCookies();

    /**
     * @param string $name
     * @param string $value
     *
     * @return $this
     */
    public function addHeader($name, $value);

    /**
     * @return $this
     */
    public function removeHeader($name);

    /**
     * @return array
     */
    public function getHeaders();

    /**
     * @param string $name
     *
     * @return string
     */
    public function getHeader($name, $default = null);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasHeader($name);

    /**
     * @return array
     */
    public function getPostParams();

    /**
     * @return array
     */
    public function getQueryParams();

    /**
     * @param bool $asResource
     *
     * @return string|resource
     */
    public function getContent($asResource = false);

    /**
     * @return string
     */
    public function getClientIp();
}