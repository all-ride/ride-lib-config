<?php

namespace ride\library\config\parser;

use ride\library\config\exception\ConfigException;

/**
 * Parser implementation for the different JSON format
 */
class JsonParser implements Parser {

    /**
     * Parse to provided configuration string to a php array
     * @param string $string Configuration string to parse
     * @return array Configuration array
     * @throws \ride\library\config\exception\ConfigException when the string
     * could not be parsed
     */
    public function parseToPhp($string) {
        if ($string == '') {
            return array();
        }

        $string = $this->removeComments($string);

        $result = json_decode($string, true);
        if ($result !== null) {
            return $result;
        }

        if (function_exists('json_last_error_msg')) {
            $error = json_last_error_msg();
            if ($error) {
                throw new ConfigException("Could not parse the provided JSON string: " . $error);
            }
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
     * Removes comments from the provided string
     * @param string $string String to process
     * @return string String without comments
     */
    protected function removeComments($json) {
        return preg_replace("#(/\*([^*]|[\r\n]|(\*+([^*/]|[\r\n])))*\*+/)|([\s\t]//.*)|(^//.*)#", '', $json);
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
