<?php

namespace ride\library\config\parser;

use \PHPUnit_Framework_TestCase;

abstract class AbstractParserTest extends PHPUnit_Framework_TestCase {

    /**
     * @var ride\library\config\parser\Parser
     */
    protected $parser;

    public function setUp() {
        $this->parser = $this->getParser();
    }

    abstract protected function getParser();

    /**
     * @dataProvider providerParseToPhp
     */
    public function testParseToPhp($php, $data) {
        $result = $this->parser->parseToPhp($data);

        $this->assertEquals($php, $result);
    }

    public function providerParseToPhp() {
        return array();
    }

    /**
     * @dataProvider providerParseFromPhp
     */
    public function testParseFromPhp($php, $data) {
        $result = $this->parser->parseFromPhp($php);

        $this->assertEquals($data, $result);
    }

    public function providerParseFromPhp() {
        return array();
    }

}
