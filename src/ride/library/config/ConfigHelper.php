<?php

namespace ride\library\config;

use ride\library\config\exception\ConfigException;

/**
 * Helper for configuration actions
 */
class ConfigHelper {

    /**
     * Sets a value to a hieraric array
     * @param array $config Hierarchic array with configuration values
     * @param string $key Configuration key to add
     * @param mixed $value Value to add
     * @return null
     */
    public function setValue(array &$config, $key, $value) {
        if (!is_string($key) || !$key) {
            throw new ConfigException('Could not set value: provided key is empty or invalid');
        }

        $data = &$config;

        $tokens = explode(Config::TOKEN_SEPARATOR, $key);
        $numTokens = count($tokens);
        for ($index = 0; $index < $numTokens; $index++) {
            $token = $tokens[$index];
            if ($index == $numTokens - 1) {
                $dataKey = $token;

                break;
            }

            if (isset($data[$token]) && is_array($data[$token])) {
                $data = &$data[$token];
            } else {
                $data[$token] = array();
                $data = &$data[$token];
            }
        }

        $data[$dataKey] = $value;
    }

    /**
     * Parses a hierarchic array into a flat array
     * @param array $config Hierarchic array with configuration values
     * @param string $prefix Prefix for the keys of the configuration array
     * (needed for recursive calls)
     * @return array Flat array of the provided configuration
     */
    public function flattenConfig(array $config, $prefix = null) {
        $result = array();

        if ($prefix) {
            $prefix .= Config::TOKEN_SEPARATOR;
        }

        foreach ($config as $key => $value) {
            $prefixedKey = $prefix . $key;

            if (is_array($value)) {
                $result = $this->flattenConfig($value, $prefixedKey) + $result;
            } else {
                $result[$prefixedKey] = $value;
            }
        }

        return $result;
    }

}