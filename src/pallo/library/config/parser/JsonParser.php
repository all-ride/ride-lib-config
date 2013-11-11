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
        if ($result !== null) {
            return $result;
        }

        switch (json_last_error()) {
        	case JSON_ERROR_DEPTH:
        	    $message = 'maximum stack depth exceeded';

        	    break;
        	case JSON_ERROR_STATE_MISMATCH:
        	    $message = 'underflow or the modes mismatch';

        	    break;
        	case JSON_ERROR_CTRL_CHAR:
        	    $message = 'unexpected control character found';

        	    break;
        	case JSON_ERROR_SYNTAX:
        	    $message = 'syntax error, malformed JSON';

        	    break;
        	case JSON_ERROR_UTF8:
        	    $message = 'malformed UTF-8 characters, possibly incorrectly encoded';

        	    break;
        	default:
        	    $message = 'unknown error';

        	    break;
        }

        throw new ConfigException("Could not parse the provided JSON string: " . $message);
    }

    /**
     * Parse the provided configuration array
     * @param array $var Configuration array
     * @return string Configuration string
     */
    public function parseFromPhp(array $var) {
        if (defined('JSON_PRETTY_PRINT')) {
            return json_encode($var, JSON_PRETTY_PRINT);
        } else {
            return json_encode($var);
        }
    }

}