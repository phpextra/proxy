<?php

/**
 * Copyright (c) 2013 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Voter;

use Psr\Log\LoggerAwareInterface;

/**
 * The VoterStackInterface interface
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
interface VoterStackInterface extends VoterInterface, LoggerAwareInterface
{
    /**
     * @param VoterInterface $strategy
     *
     * @return $this
     */
    public function addVoter(VoterInterface $voter);

} 