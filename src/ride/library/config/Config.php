<?php

namespace ride\library\config;

/**
 * Configuration data container
 *
 * The configuration is defined by key-value pairs. The key is a . separated
 * string. The first token of the key is called the section.
 *
 * eg.
 * database.connection.test = mysql://localhost/test
 */
interface Config {

    /**
     * Separator between the tokens of the configuration key
     * @var string
     */
    const TOKEN_SEPARATOR = '.';

    /**
     * Gets the complete configuration as a tree
     * @return array Tree like array with each configuration key token as a
     * array key
     */
    public function getAll();

    /**
     * Gets a configuration value
     * @param string $key Configuration key
     * @param mixed $default Default value for when the configuration key is
     * not set
     * @return mixed Configuration value if set, the provided default
     * value otherwise
     * @throws ride\library\config\exception\ConfigException when the key is empty
     * or not a string
     */
    public function get($key, $default = null);

    /**
     * Sets a configuration value
     * @param string $key Configuration key
     * @param mixed $value Value for the configuration key
     * @return null
     * @throws ride\library\config\exception\ConfigException when the key is
     * empty or not a string
     */
    public function set($key, $value);

}