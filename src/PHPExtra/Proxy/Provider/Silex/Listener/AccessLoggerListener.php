<?php

namespace PHPExtra\Proxy\Provider\Silex\Listener;

use PHPExtra\Proxy\Event\ProxyRequestEvent;
use PHPExtra\Proxy\Event\ProxyResponseEvent;
use PHPExtra\Proxy\EventListener\ProxyListenerInterface;
use PHPExtra\Proxy\Http\RequestInterface;
use PHPExtra\Proxy\Http\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * The ProxyResponseEventLoggerListener class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class AccessLoggerListener implements ProxyListenerInterface
{
    const STOPWATCH_EVENT = 'proxy-benchmark';

    /**
     * @var Stopwatch
     */
    private $stopwatch;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param LoggerInterface $logger
     * @param Stopwatch       $stopwatch
     */
    function __construct(LoggerInterface $logger, Stopwatch $stopwatch)
    {
        $this->logger = $logger;
        $this->stopwatch = $stopwatch;
    }

    /**
     * @priority HIGHEST
     * @param ProxyRequestEvent $event
     */
    public function onProxyRequest(ProxyRequestEvent $event)
    {
        $this->stopwatch->start(self::STOPWATCH_EVENT);
    }

    /**
     * @priority MONITOR
     * @param ProxyResponseEvent $event
     */
    public function onProxyResponse(ProxyResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $logLine = $this->getLogLine($this->getLogData($request, $response));

        $level = LogLevel::INFO;
        if(!$response || !$response->isSuccessful()){
            $level = LogLevel::WARNING;
        }

        $this->logger->log($level, $logLine);
    }

    /**
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     *
     * @return array
     */
    protected function getLogData(RequestInterface $request, ResponseInterface $response = null)
    {
        $time = $this->stopwatch->stop(self::STOPWATCH_EVENT)->getDuration();

        $uagent = $request->getHeader('User-Agent', '-');
        $uagent = $uagent[0];

        $xcache = $response && $response->hasHeaderWithValue('x-cache', 'HIT') ? 'HIT' : 'MISS';

        $data = array();
        $data[] = $xcache;
        $data[] = bcdiv($time, 1000, 4); // milliseconds
        $data[] = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1'; // @todo ip is always set to 127.0.0.1 due to broken request object
        $data[] = $request->getMethod();
        $data[] = $request->getUri();
        $data[] = $response ? $response->getStatusCode() : '-'; // bytes
        $data[] = $response ? $response->getLength() : '-'; // bytes
        $data[] = sprintf('"%s"', $uagent);

        return $data;
    }

    /**
     * @param array $data
     *
     * @return string
     */
    protected function getLogLine(array $data)
    {
        return implode(' ', $data);
    }
}