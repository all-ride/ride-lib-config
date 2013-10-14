<?php

namespace pallo\library\config\io;

use pallo\library\config\exception\ConfigException;
use pallo\library\system\file\browser\FileBrowser;

/**
 * Abstract implementation of a IO
 */
abstract class AbstractIO {

    /**
     * Instance of the file browser
     * @var pallo\library\system\file\browser\FileBrowser
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
     * Constructs a new abstract IO
     * @param pallo\library\system\file\browser\FileBrowser $fileBrowser
     * @param string $file Name of the file
     * @param string $path Relative path in the file browser
     * @return null
     */
    public function __construct(FileBrowser $fileBrowser, $file, $path = null) {
        $this->fileBrowser = $fileBrowser;

        $this->setFile($file);
        $this->setPath($path);
    }

    /**
     * Sets the name of the configuration file
     * @param string $file
     * @throws pallo\library\config\exception\ConfigException
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
     * @throws pallo\library\config\exception\ConfigException
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
     * @throws pallo\library\config\exception\ConfigException when the provided
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

}