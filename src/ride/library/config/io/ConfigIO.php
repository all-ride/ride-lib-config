<?php

namespace ride\library\config\io;

/**
 * Interface for the input/output implementation of the configuration
 */
interface ConfigIO {

    /**
     * Gets the names of all the sections in the configuration
     * @return array Array with the names of all sections in the configuration
     */
    public function getAllSections();

    /**
     * Gets the complete configuration
     * @return array Hierarchic array with each configuration token as a key
     */
    public function getAll();

    /**
     * Gets a section from the configuration
     * @param string $section
     * @return array Hierarchic array with each configuration token as a key
     */
    public function get($section);

    /**
     * Sets a configuration value
     * @param string $key key of the configuration value
     * @param mixed $value
     * @return null
     */
    public function set($key, $value);

}