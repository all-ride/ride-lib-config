<?php

namespace ride\library\config\parser;

/**
 * Parser implementation for the INI format
 */
class IniParser implements Parser {

    /**
     * Prefix for reserved words
     * @var string
     */
    const RESERVED_PREFIX = 'ZZZ';

    /**
     * Suffix for reserved words
     * @var string
     */
    const RESERVED_SUFFIX = 'ZZZ';

    /**
     * Reserved words of the PHP ini parser
     * @var array
     */
    protected $reservedWords = array(
        'null' => true,
        'yes' => true,
        'no' => true,
        'true' => true,
        'false' => true,
        'on' => true,
        'off' => true,
        'none' => true,
    );
    // reserved chars: {}|&~![()^"

    /**
     * Parse to provided configuration string to a php array
     * @param string $string Configuration string to parse
     * @return array Configuration array
     */
    public function parseToPhp($string) {
        // parse the ini string into an array
        $ini = @parse_ini_string($string, true);

        if ($ini !== false) {
            return $ini;
        }

        // the ini string could not be parsed, let's prefix and suffix the
        // reserved words and try again
        $string = $this->replaceReservedWords($string);

        $ini = @parse_ini_string($string, true, INI_SCANNER_RAW);
        if ($ini === false) {
            $error = error_get_last();

            throw new ConfigException('Could not parse the provided ini: ' . $error['message']);
        }

        $ini = $this->revertReservedWordsFromIni($ini);

        return $ini;
    }

    /**
     * Parse the provided configuration array
     * @param array $var Configuration array
     * @return string Configuration string
     */
    public function parseFromPhp(array $var) {
        return $this->parseToIni($var);
    }

    /**
     * Gets the ini string for the provided configuration
     * @param mixed $var Hierarchic array with each configuration token
     * as a key or a value
     * @param string $key Key for the provided values (for recursive calls)
     * @return string Ini of the provided config
     * @throws ride\library\config\exception\ConfigException when the provided
     * config is not an array and no key is provided
     */
    protected function parseToIni($var, $key = null) {
        $output = '';

        if (is_array($var)) {
            foreach ($var as $k => $v) {
                $newKey = is_null($key) ? $k : $key . Config::TOKEN_SEPARATOR . $k;

                $output .= $this->parseToIni($v, $newKey);
            }
        } elseif (is_null($key)) {
            throw new ConfigException('Could not parse provided variable to INI: no key provided, make sure $var is an array if you leave $key empty.');
        } else {
            if (is_null($var)) {
                return $output;
            } elseif (is_bool($var)) {
                $var = $var === true ? '1' : '0';
            } elseif (!ctype_alnum($var)) {
                $var = addslashes($var);
                $var = '"' . $var . '"';
            }

            $output .= $key . ' = ' . $var . "\n";
        }

        return $output;
    }

    /**
     * Adds the prefix and suffix to the reserved words
     * @param string $string String to parse
     * @return string Parsed string
     */
    protected function replaceReservedWords($string) {
        foreach ($this->reservedWords as $reservedWord => $null) {
            $string = str_replace($reservedWord, self::RESERVED_PREFIX . $reservedWord . self::RESERVED_SUFFIX, $string);
            $string = str_replace($reservedWord, self::RESERVED_PREFIX . strtoupper($reservedWord) . self::RESERVED_SUFFIX, $string);
        }

        return $string;
    }

    /**
     * Removes the prefix and suffix from the reserved words
     * @param string $string String to parse
     * @return string Parsed string
     */
    protected function revertReservedWords($string) {
        foreach ($this->reservedWords as $reservedWord => $null) {
            $string = str_replace(self::RESERVED_PREFIX . $reservedWord . self::RESERVED_SUFFIX, $reservedWord, $string);
            $string = str_replace(self::RESERVED_PREFIX . strtoupper($reservedWord) . self::RESERVED_SUFFIX, $reservedWord, $string);
        }

        return $string;
    }

    /**
     * Unparse the reserved words from the provided ini
     * @param array $ini
     * @return array
     */
    protected function revertReservedWordsFromIni(array $ini) {
        $parsedIni = array();

        foreach ($ini as $key => $value) {
            $this->revertReservedWords($key);

            if (is_array($value)) {
                $value = $this->revertReservedWordsFromIni($value);
            } else {
                $value = $this->revertReservedWords($value);
            }

            $parsedIni[$key] = $value;
        }

        return $parsedIni;
    }

}