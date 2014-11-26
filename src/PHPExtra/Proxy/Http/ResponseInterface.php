<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Http;

/**
 * The ResponseInterface class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface ResponseInterface extends HttpMessageInterface
{
    /**
     * Length in bytes
     *
     * @return int
     */
    public function getLength();

    /**
     * Get HTTP status code
     *
     * @return int
     */
    public function getStatusCode();

    /**
     * Get response body
     *
     * @return string
     */
    public function getBody();

    /**
     * @return string
     */
    public function getCharset();

    /**
     * @return bool
     */
    public function isSuccessful();

    /**
     * @return bool
     */
    public function isRedirection();

    /**
     * @return bool
     */
    public function isClientError();

    /**
     * @return bool
     */
    public function isServerError();

    /**
     * @return bool
     */
    public function isOk();

    /**
     * @return bool
     */
    public function isNotFound();

    /**
     * @return bool
     */
    public function isForbidden();

    /**
     * @return bool
     */
    public function isEmpty();

    /**
     * Returns true if ttl is > 0
     * TTL is dateExpire - responseDate
     *
     * @return bool
     */
    public function isFresh();

    /**
     * Returns true if response is marked private
     *
     * @return bool
     */
    public function isPrivate();

    /**
     * Get max response age in seconds
     *
     * @return int
     */
    public function getMaxAge();

    /**
     * Get expected lifetime in seconds
     * Can return null
     *
     * @return int
     */
    public function getTtl();

    /**
     * @return \DateTime
     */
    public function getDate();

    /**
     * Send request to output buffer
     *
     * @return $this
     */
    public function send();

    /**
     * @param \DateTime $date
     *
     * @return mixed
     */
    public function setDate(\DateTime $date);

}