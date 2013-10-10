<?php

namespace pallo\library\config;

use pallo\library\config\exception\ConfigException;
use pallo\library\config\io\ConfigIO;
use pallo\library\config\ConfigHelper;

/**
 * Generic config implementation
 */
class GenericConfig implements Config {

    /**
     * Configuration input/output implementation
     * @var pallo\library\config\io\ConfigIO
     */
    protected $io;

    /**
     * Helper for the configuration actions
     * @var pallo\library\config\ConfigHelper
     */
    protected $helper;

    /**
     * Array with the loaded configuration
     * @var array
     */
    protected $data;

    /**
     * Constructs a new configuration container
     * @param pallo\library\config\io\ConfigIO $io Configuration input/output
     * implementation
     * @param pallo\library\config\ConfigHelper $helper Helper for the
     * configuration actions
     * @return null
     */
    public function __construct(ConfigIO $io, ConfigHelper $helper = null) {
        $this->io = $io;
        $this->helper = $helper;
        $this->data = array();
    }

    /**
     * Gets the config helper
     * @return pallo\library\config\ConfigHelper
     */
    public function getConfigHelper() {
        if (!$this->helper) {
            $this->helper = new ConfigHelper();
        }

        return $this->helper;
    }

    /**
     * Gets the complete configuration as a tree
     * @return array Tree like array with each configuration key token as a
     * array key
     */
    public function getAll() {
        return $this->data = $this->io->getAll();
    }

    /**
     * Gets a configuration value
     * @param string $key Configuration key
     * @param mixed $default Default value for when the configuration key is
     * not set
     * @return mixed Configuration value if set, the provided default value
     * otherwise
     * @throws pallo\library\config\exception\ConfigException when the key is
     * empty or not a string
     */
    public function get($key, $default = null) {
        $tokens = $this->getKeyTokens($key);

        if (count($tokens) === 1) {
            if (empty($this->data[$key])) {
                return $default;
            }

            return $this->data[$key];
        }

        $result = &$this->data;
        foreach ($tokens as $token) {
            if (!isset($result[$token])) {
                return $default;
            }

            $result = &$result[$token];
        }

        return $result;
    }

    /**
     * Sets a configuration value
     * @param string $key Configuration key
     * @param mixed $value Value for the configuration key
     * @return null
     * @throws zibo\library\config\exception\ConfigException when the key is
     * empty or not a string
     */
    public function set($key, $value) {
        // make sure the section is read
        $tokens = $this->getKeyTokens($key);

        $this->getConfigHelper()->setValue($this->data, $key, $value);

        $this->io->set($key, $value);
    }

    /**
     * Gets the tokens of a configuration key. This method will read the
     * configuration for the section token (first token) if it has not been read before.
     * @param string $key The configuration key
     * @return array Array with the tokens of the configuration key
     */
    protected function getKeyTokens($key) {
        if (!is_string($key) || !$key) {
            throw new ConfigException('Provided key is empty or invalid');
        }

        $tokens = explode(Config::TOKEN_SEPARATOR, $key);

        $section = $tokens[0];
        if (!isset($this->data[$section])) {
            $this->data[$section] = $this->io->get($section);
        }

        return $tokens;
    }

}