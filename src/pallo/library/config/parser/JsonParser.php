<?php

namespace pallo\library\config\parser;

use pallo\library\config\exception\ConfigException;
/**
 * Parser implementation for the different JSON format
 */
class JsonParser implements Parser {

    /**
     * Parse to provided configuration string to a php array
     * @param string $string Configuration string to parse
     * @return array Configuration array
     */
    public function parseToPhp($string) {
        $result = json_decode($string, true);
        if ($result === null) {
            throw new ConfigException("Could not parse the provided JSON string");
        }

        return $result;
    }

    /**
     * Parse the provided configuration array
     * @param array $var Configuration array
     * @return string Configuration string
     */
    public function parseFromPhp(array $var) {
        return json_encode($var);
    }

}