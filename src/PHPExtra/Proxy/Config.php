<?php

namespace PHPExtra\Proxy;

/**
 * The Config class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class Config implements ConfigInterface
{
    /**
     * @var string
     */
    private $proxyName;

    /**
     * @var string
     */
    private $proxyVersion;

    /**
     * @var string
     */
    private $secret;

    /**
     * @var array
     */
    private $hosts = array();

    /**
     * @var string
     */
    private $resourcePath;

    /**
     * @var boolean
     */
    private $isStallingResponsesEnabled = true;

    /**
     * @param array $config
     */
    function __construct(array $config = array())
    {
        $default = array(
            'name' => 'PHPExtraProxyServer',
            'version' => '1.0.0',
            'secret' => md5(__FILE__),
            'hosts' => array(
                array('localhost', 80),
                array('127.0.0.1', 80),
            ),
            'resource_path' => __DIR__ . '/../../../resources/html/',
            'stalling_responses_enabled' => true
        );

        $config = array_merge($default, $config);

        $this->proxyName = $config['name'];
        $this->proxyVersion = $config['version'];
        $this->secret = $config['secret'];
        $this->isStallingResponsesEnabled = $config['stalling_responses_enabled'];
        $this->resourcePath = $config['resource_path'];

        foreach ($config['hosts'] as $id => $hostDetails) {
            $this->hosts[$hostDetails[1]][] = $hostDetails[0];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getSecret()
    {
        return $this->secret;
    }

    /**
     * {@inheritdoc}
     */
    public function getProxyName()
    {
        return $this->proxyName;
    }

    /**
     * {@inheritdoc}
     */
    public function getProxyVersion()
    {
        return $this->proxyVersion;
    }

    /**
     * {@inheritdoc}
     */
    public function getHostsOnPort($port = 80)
    {
        return isset($this->hosts[$port]) ? $this->hosts[$port] : array();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllHosts()
    {
        $hosts = array();
        foreach ($this->hosts as $port => $host) {
            $hosts[] = $host;
        }
        return $hosts;
    }

    /**
     * Get resource path.
     *
     * @return string
     */
    public function getResourcePath()
    {
        return $this->resourcePath;
    }

    /**
     * Check if stalling responses on error is enabled.
     *
     * @return boolean
     */
    public function isStallingResponsesEnabled()
    {
        return $this->isStallingResponsesEnabled;
    }
}