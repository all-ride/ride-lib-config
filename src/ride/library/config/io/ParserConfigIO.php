<?php

namespace ride\library\config\io;

use ride\library\config\exception\ConfigException;
use ride\library\config\parser\Parser;
use ride\library\config\ConfigHelper;
use ride\library\system\file\browser\FileBrowser;
use ride\library\system\file\File;

/**
 * Parser implementation of the configuration I/O using the Ride file browser
 */
class ParserConfigIO extends AbstractIO implements ConfigIO {

    /**
     * Instance of the config helper
     * @var \ride\library\config\ConfigHelper
     */
    protected $helper;

    /**
     * Instance of the parser for the configuration format
     * @var \ride\library\config\parser\Parser
     */
    protected $parser;

    /**
     * Loaded configuration
     * @var array
     */
    protected $config;

    /**
     * @var
     */
    protected $variables;

    /**
     * Constructs a new Ride configuration I/O
     * @return null
     */
    public function __construct(FileBrowser $fileBrowser, ConfigHelper $configHelper, Parser $parser, $file, $path = null) {
        parent::__construct($fileBrowser, $file, $path);

        $this->helper = $configHelper;
        $this->parser = $parser;

        $this->config = null;
    }

    /**
     * Gets the complete configuration
     * @return array Hierarchic array with each configuration token as a key
     */
    public function getAll() {
        if ($this->config === null) {
            $this->read();
        }

        return $this->config;
    }

    /**
     * Gets the configuration values for a section
     * @param string $section Name of the section
     * @return array Hierarchic array with each configuration token as a key
     * @throws \ride\library\config\exception\ConfigException when the section
     * name is invalid or empty
     */
    public function get($section) {
        if (!is_string($section) || !$section) {
            throw new ConfigException('Could not get section: provided section name is empty or invalid');
        }

        if ($this->config === null) {
            $this->read();
        }

        if (!isset($this->config[$section])) {
            return array();
        }

        return $this->config[$section];
    }

    /**
     * Reads the configuration
     * @return null
     */
    protected function read() {
        $this->config = array();
        $this->variables = array(
            'application' => $this->fileBrowser->getApplicationDirectory(),
            'environment' => $this->environment,
            'path' => null,
            'public' => $this->fileBrowser->getPublicDirectory(),
        );

        $this->readFiles($this->config, $this->file);

        if ($this->environment) {
            $this->readFiles($this->config, $this->environment . File::DIRECTORY_SEPARATOR . $this->file);
        }

        unset($this->variables);
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
            $path = $file->getAbsolutePath();
            $path = str_replace('/' . $fileName, '', $path);

            $this->variables['path'] = $path;

            $this->readFile($config, $file);
        }
    }

    /**
     * Read the configuration values for the provided file and add them to the provided values array
     * @param array $configArray with the values which are already read
     * @param \ride\library\system\file\File $file file to read and parse
     * @return null
     * @throws \ride\library\config\exception\ConfigException when the provided
     * file could not be read
     */
    protected function readFile(array &$config, File $file) {
        try {
            $parameters = $file->read();

            $parameters = $this->parser->parseToPhp($parameters);
            $parameters = $this->helper->flattenConfig($parameters);

            foreach ($parameters as $key => $value) {
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
     * @throws \ride\library\config\exception\ConfigException when the provided
     * key is invalid or empty
     */
    public function set($key, $value) {
        if (!is_string($key) || $key == '') {
            throw new ConfigException('Could not set configuration value: provided key is empty or invalid');
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
        $file = $path->getChild($this->file);

        // gets the existing values from the file
        $values = array();
        if ($file->exists()) {
            $this->readFile($values, $file);
        }

        // set the new configuration value
        $this->helper->setValue($values, $key, $value);
        $values = $this->helper->flattenConfig($values);
        foreach ($values as $i => $v) {
            if ($v === null) {
                unset($values[$i]);
            }
        }

        // write the file
        $config = $this->parser->parseFromPhp($values);
        if ($config) {
            $file->write($config);
        } elseif ($file->exists()) {
            $file->delete();
        }

        // set the value in this io
        if ($this->config !== null) {
            $this->helper->setValue($this->config, $key, $value);
        }
    }

}
