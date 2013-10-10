<?php

namespace pallo\library\config\io;

use pallo\library\config\exception\ConfigException;
use pallo\library\config\parser\Parser;
use pallo\library\config\ConfigHelper;
use pallo\library\config\Config;
use pallo\library\system\file\browser\FileBrowser;
use pallo\library\system\file\File;

/**
 * Parser implementation of the configuration I/O using the Pallo file browser
 */
class ParserConfigIO implements ConfigIO {

    /**
     * Instance of the file browser
     * @var pallo\library\system\file\browser\FileBrowser
     */
    protected $fileBrowser;

    /**
     * Instance of the config helper
     * @var pallo\library\config\ConfigHelper
     */
    protected $helper;

    /**
     * Instance of the parser for the configuration format
     * @var pallo\library\config\parser\Parser
     */
    protected $parser;

    /**
     * Extension of the files
     * @var string
     */
    protected $extension;

    /**
     * Relative path of the files
     * @var string
     */
    protected $path;

    /**
     * Name of the environment
     * @var string
     */
    protected $environment;

    /**
     * Constructs a new Pallo configuration I/O
     * @return null
     */
    public function __construct(FileBrowser $fileBrowser, ConfigHelper $configHelper, Parser $parser, $extension, $path = null) {
        $this->fileBrowser = $fileBrowser;
        $this->helper = $configHelper;
        $this->parser = $parser;

        $this->setExtension($extension);
        $this->setPath($path);
    }

    /**
     * Sets the extension for configuration files of this IO
     * @param string $extension
     * @throws pallo\library\config\exception\ConfigException
     */
    public function setExtension($extension) {
        if (!is_string($extension) || $extension == '') {
            throw new ConfigException('Could not set the extension: provided extension is empty or invalid');
        }

        $this->extension = $extension;
    }

    /**
     * Gets the extension for configuration files of this IO
     * @return string
     */
    public function getExtension() {
        return $this->extension;
    }

    /**
     * Sets the relative path for configuration files of this IO
     * @param string $path
     * @throws pallo\library\config\exception\ConfigException
     */
    public function setPath($path) {
        if (!is_string($path) || $path == '') {
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
     * @throws Exception when the provided name is empty or not a string
     */
    public function setEnvironment($environment = null) {
        if ($environment !== null && (!is_string($environment) || !$environment)) {
            throw new Exception('Could not set the environment: provided environment is empty or not a string');
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
     * Get the names of all the sections in the configuration
     * @return array Array with the names of all the ini files in the
     * configuration directory, withouth the extension
     */
    public function getAllSections() {
        $sections = array();

        $includeDirectories = $this->fileBrowser->getIncludeDirectories();
        foreach ($includeDirectories as $directory) {
            if ($this->path) {
                $directory = $directory->getChild($this->path);
            }

            $sections = $this->getSectionsFromPath($directory) + $sections;

            if ($this->environment) {
                $sections = $this->getSectionsFromPath($directory->getChild($this->environment)) + $sections;
            }
        }

        return array_keys($sections);
    }

    /**
     * Get the names of the sections in the provided path
     * @param pallo\library\system\file\File $path
     * @return array Array with the file names of all the sections, without
     * the extension, as key
     */
    protected function getSectionsFromPath(File $path) {
        $sections = array();

        if (!$path->exists()) {
            return $sections;
        }

        $extensionLength = strlen($this->extension);

        $files = $path->read();
        foreach ($files as $file) {
            if ($file->isDirectory() || $file->getExtension() != $this->extension) {
                continue;
            }

            $sectionName = substr($file->getName(), 0, ($extensionLength + 1) * -1);

            $sections[$sectionName] = true;
        }

        return $sections;
    }

    /**
     * Gets the complete configuration
     * @return array Hierarchic array with each configuration token as a key
     */
    public function getAll() {
        $all = array();

        $sections = $this->getAllSections();
        foreach ($sections as $section) {
            $all[$section] = $this->get($section);
        }

        return $all;
    }

    /**
     * Gets the configuration values for a section
     * @param string $section Name of the section
     * @return array Hierarchic array with each configuration token as a key
     * @throws pallo\library\config\exception\ConfigException when the section
     * name is invalid or empty
     */
    public function get($section) {
        if (!is_string($section) || !$section) {
            throw new ConfigException('Could not get section: provided section name is empty or invalid');
        }

        $config = array();
        $fileName = $section . '.' . $this->extension;

        $this->variables = array(
            'application' => $this->fileBrowser->getApplicationDirectory(),
            'environment' => $this->environment,
            'path' => null,
            'public' => $this->fileBrowser->getPublicDirectory(),
        );

        $this->readFiles($config, $fileName);

        if ($this->environment) {
            $this->readFiles($config, $this->environment . File::DIRECTORY_SEPARATOR . $fileName);
        }

        $this->variables = null;

        return $config;
    }

    /**
     * Reads the configuration files with the provided file name
     * @param array $config Configuration to set the result to
     * @param string $fileName Relative file name of the configuration
     * @return null
     */
    protected function readFiles(array &$config, $fileName) {
        if ($this->path) {
            $fileName = $this->path . File::DIRECTORY_SEPARATOR . $fileName;
        }

        $files = array_reverse($this->fileBrowser->getFiles($fileName));
        foreach ($files as $file) {
            $this->variables['path'] = $file->getParent()->getPath();

            $this->readFile($config, $file);
        }
    }

    /**
     * Read the configuration values for the provided file and add them to the provided values array
     * @param array $configArray with the values which are already read
     * @param pallo\library\system\file\File $file file to read and parse
     * @return null
     * @throws pallo\library\config\exception\ConfigException when the provided
     * file could not be read
     */
    protected function readFile(array &$config, File $file) {
        try {
            $ini = $file->read();

            $ini = $this->parser->parseToPhp($ini);
            $ini = $this->helper->flattenConfig($ini);

            foreach ($ini as $key => $value) {
                $value = $this->parseVariables($value);

                $this->helper->setValue($config, $key, $value);
            }
        } catch (Exception $exception) {
            throw new ConfigException('Could not read config from ' . $file, 0, $exception);
        }
    }

    /**
     * Parses the variables into the provided value
     * @param string $string Value to parse the variables into
     * @param string $varDelimiter Prefix and suffix of a variable name
     * @return string Provided value with the variables parsed into
     */
    protected function parseVariables($string, $varDelimiter = '%') {
        if (!is_string($string) || !$string|| !isset($this->variables)) {
            return $string;
        }

        foreach ($this->variables as $variable => $value) {
            $string = str_replace($varDelimiter . $variable . $varDelimiter, $value, $string);
        }

        return $string;
    }

    /**
     * Sets a configuration value to the data source
     * @param string $key Configuration key
     * @param mixed $value Value to write
     * @return null
     * @throws pallo\library\config\exception\ConfigException when the provided
     * key is invalid or empty
     */
    public function set($key, $value) {
        if (!is_string($key) || $key == '') {
            throw new ConfigException('Could not set configuration value: provided key is empty or invalid');
        }

        $tokens = explode(Config::TOKEN_SEPARATOR, $key);
        if (count($tokens) < 2) {
            throw new ConfigException('Could not set ' . $key . ': key should have at least 2 tokens (eg system.memory). Use ' . Config::TOKEN_SEPARATOR . ' as a token separator.');
        }

        $path = $this->fileBrowser->getApplicationDirectory();

        if ($path === null) {
            throw new ConfigException('Could not set ' . $key . ': no write path set');
        }

        if ($this->path) {
            $path = $path->getChild($this->path);
        }

        if (!$path->isWritable()) {
            throw new ConfigException('Could not set ' . $key . ': write path ' . $path . ' is not writable');
        }

        // make sure the path exists
        $path->create();

        // gets the file, based on the section of the key
        $file = array_shift($tokens) . '.' . $this->extension;
        $file = $path->getChild($file);

        // gets the existing values from the file
        $values = array();
        if ($file->exists()) {
            $this->readFile($values, $file);
        }

        // add the new configuration value
        $key = implode(Config::TOKEN_SEPARATOR, $tokens);
        $this->helper->setValue($values, $key, $value);

        // write the file
        $config = $this->parser->parseFromPhp($values);

        if ($config) {
            $file->write($config);
        } elseif ($file->exists()) {
            $file->delete();
        }
    }

}