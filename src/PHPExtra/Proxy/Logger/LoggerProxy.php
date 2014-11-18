<?php

namespace PHPExtra\Proxy\Logger;

use Psr\Log\AbstractLogger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

/**
 * Wraps the logger (for the purpose of easy switching)
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class LoggerProxy extends AbstractLogger implements LoggerAwareInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     */
    function __construct(LoggerInterface $logger = null)
    {
        $this->logger = $logger;
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
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = array())
    {
        if($this->logger){
            $this->logger->log($level, $message, $context);
        }
        return $this;
    }
}