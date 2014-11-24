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
     * {@inheritdoc}
     */
    public function getUri();

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
    public function getPathInfo();

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
     * Return host. If no host was set it uses few searching techniques to detect the real host for this request.
     *
     * @return string
     */
    public function getHost();

    /**
     * Get host header value
     * This might be different than host set using setHost method
     *
     * @return string|null
     */
    public function getHostHeader();

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