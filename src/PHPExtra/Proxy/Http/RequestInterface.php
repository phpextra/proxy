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
interface RequestInterface extends HttpMessageInterface
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
    public function getContentType();

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
     * @return array
     */
    public function getETags();

    /**
     * @return string
     */
    public function getPort();

    /**
     * @return array
     */
    public function getCookies();

    /**
     * @return array
     */
    public function getPostParams();

    /**
     * @return array
     */
    public function getQueryParams();

    /**
     * @return string
     */
    public function getClientIp();

    /**
     * Get the max accepted response age
     *
     * @return \DateTime
     */
    public function getMaxAge();

    /**
     * @return bool
     */
    public function isNoCache();
}