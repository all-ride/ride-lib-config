<?php

namespace ride\library\config\io;

use ride\library\config\exception\ConfigException;
use ride\library\config\Config;
use ride\library\system\file\browser\FileBrowser;

/**
 * Abstract implementation of a IO
 */
abstract class AbstractIO {

    /**
     * Instance of the file browser
     * @var \ride\library\system\file\browser\FileBrowser
     */
    protected $fileBrowser;

    /**
     * Name of the configuration file
     * @var string
     */
    protected $file;

    /**
     * Relative path for the configuration file
     * @var string
     */
    protected $path;

    /**
     * Name of the environment
     * @var string
     */
    protected $environment;

    /**
     * Instance of the configuration
     * @var \ride\library\config\Config
     */
    protected $config;

    /**
     * Constructs a new abstract IO
     * @param \ride\library\system\file\browser\FileBrowser $fileBrowser
     * @param string $file Name of the file
     * @param string $path Relative path in the file browser
     * @return null
     */
    public function __construct(FileBrowser $fileBrowser, $file, $path = null) {
        $this->fileBrowser = $fileBrowser;
        $this->config = null;

        $this->setFile($file);
        $this->setPath($path);
    }

    /**
     * Sets the configuration
     * @param \ride\library\config\Config $config
     * @return null
     */
    public function setConfig(Config $config) {
        $this->config = $config;
    }

    /**
     * Sets the name of the configuration file
     * @param string $file
     * @throws \ride\library\config\exception\ConfigException
     */
    public function setFile($file) {
        if ($file !== null && (!is_string($file) || $file == '')) {
            throw new ConfigException('Could not set the file: provided file is empty or invalid');
        }

        $this->file = $file;
    }

    /**
     * Gets the name of the configuration file
     * @return string
     */
    public function getFile() {
        return $this->file;
    }

    /**
     * Sets the relative path for configuration files of this IO
     * @param string $path
     * @throws \ride\library\config\exception\ConfigException
     */
    public function setPath($path) {
        if ($path !== null && (!is_string($path) || $path == '')) {
            throw new ConfigException('Could not set the path: provided path is empty or invalid');
        }

        $this->path = $path;
    }

    /**
     * Gets the relative path for the configuration files of this IO
     * @return string
     */
    public function getPath() {
        return $this->path;
    }

    /**
     * Sets the name of the environment
     * @param string $environment Name of the environment
     * @return null
     * @throws \ride\library\config\exception\ConfigException when the provided
     * name is empty or not a string
     */
    public function setEnvironment($environment = null) {
        if ($environment !== null && (!is_string($environment) || !$environment)) {
            throw new ConfigException('Could not set the environment: provided environment is empty or not a string');
        }

        $this->environment = $environment;
    }

    /**
     * Gets the name of the environment
     * @return string|null
     */
    public function getEnvironment() {
        return $this->environment;
    }

    /**
     * Gets a parameter value if applicable (delimited by %)
     * @param string $parameter Parameter string
     * @return string Provided parameter if not a parameter string, the
     * parameter value otherwise
     * @throws \ride\library\config\exception\ConfigException when no
     * configuration set
     */
    protected function processParameter($parameter) {
        if (!$this->config) {
            throw new ConfigException('Could not process the parameter: no configuration set, invoke setConfig() first');
        }

        if (substr($parameter, 0, 1) != '%' || substr($parameter, -1) != '%') {
            return $parameter;
        }

        $parameter = substr($parameter, 1, -1);

        if (strpos($parameter, '|') !== false) {
            list($key, $default) = explode('|', $parameter, 2);
        } else {
            $key = $parameter;
            $default = null;
        }

        return $this->config->get($key, $default);
    }

}