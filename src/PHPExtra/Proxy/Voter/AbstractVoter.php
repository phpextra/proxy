<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Voter;

/**
 * The AbstractVoter class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
abstract class AbstractVoter implements VoterInterface
{
    /**
     * @var int
     */
    private $priority;

    /**
     * @param int $priority
     */
    function __construct($priority = self::PRIORITY_NORMAL)
    {
        $this->priority = $priority;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }
} 