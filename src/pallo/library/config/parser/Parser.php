<?php

namespace pallo\library\config\parser;

/**
 * Parser interface for different file formats
 */
interface Parser {

    /**
     * Parse to provided configuration string to a php array
     * @param string $string Configuration string to parse
     * @return array Configuration array
     */
    public function parseToPhp($string);

    /**
     * Parse the provided configuration array
     * @param array $var Configuration array
     * @return string Configuration string
     */
    public function parseFromPhp(array $var);

}