<?php

/**
 * Copyright (c) 2014 Jacek Kobus <kobus.jacek@gmail.com>
 * See the file LICENSE.txt for copying permission.
 */

namespace PHPExtra\Proxy\Voter;

use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\ResponseInterface;
use PHPExtra\Type\Collection\Collection;
use Psr\Log\LoggerInterface;

/**
 * Container containing strategies that can act as a strategy himself
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class VoterStack extends Collection implements VoterStackInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var int
     */
    private $priority = self::PRIORITY_NORMAL;

    /**
     * @param LoggerInterface $logger
     */
    function __construct(LoggerInterface $logger = null)
    {
        $this->setLogger($logger);
    }

    /**
     * {@inheritdoc}
     */
    public function addVoter(VoterInterface $voter)
    {
        $this->add($voter);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function canStoreResponseInStorage(ResponseInterface $response, RequestInterface $request)
    {
        $flag = false;
        $weight = self::PRIORITY_NORMAL;
        $usedVoter = null;

        foreach ($this as $voter) {
            /** @var VoterInterface $voter */

            $newFlag = $voter->canStoreResponseInStorage($response, $request);
            $newWeight = $voter->getPriority();

            if($newWeight >= $weight){
                // voter is strong enough to take control

                $flag = $newFlag;
                $weight = $newWeight;
                $usedVoter = $voter;

            }
        }

        if($usedVoter){

            $voterName = get_class($usedVoter);

            if($flag === false){
                $this->logger->debug(sprintf('Response cannot be stored in storage - denied by %s', get_class($usedVoter)));
            }else{
                $this->logger->debug(sprintf('Response CAN be stored in storage - allowed by %s', $voterName));
            }

        }else{
            $this->logger->debug('Response cannot be stored in storage (default)');
        }

        return $flag;
    }

    /**
     * {@inheritdoc}
     */
    public function canUseResponseFromStorage(ResponseInterface $response, RequestInterface $request)
    {
        $flag = false;
        $weight = self::PRIORITY_NORMAL;
        $usedVoter = null;

        foreach ($this as $voter) {
            /** @var VoterInterface $voter */

            $newFlag = $voter->canUseResponseFromStorage($response, $request);
            $newWeight = $voter->getPriority();

            if($newWeight >= $weight){
                // voter is strong enough to take control
                $flag = $newFlag;
                $weight = $newWeight;
                $usedVoter = $voter;
            }
        }

        if($usedVoter){

            $voterName = get_class($usedVoter);

            if($flag === false){
                $this->logger->debug(sprintf('Response cannot be served from storage - denied by %s', $voterName));
            }else{
                $this->logger->debug(sprintf('Response CAN be served from storage - allowed by %s', $voterName));
            }

        }else{
            $this->logger->debug('Response cannot be served from storage (default)');
        }

        return $flag;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @param int $priority
     *
     * @return $this
     */
    public function setPriority($priority)
    {
        $this->priority = $priority;

        return $this;
    }
}