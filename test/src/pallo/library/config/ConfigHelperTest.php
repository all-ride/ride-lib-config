<?php

namespace pallo\library\config;

use \PHPUnit_Framework_TestCase;

class ConfigHelperTest extends PHPUnit_Framework_TestCase {

    /**
     * @var pallo\library\config\ConfigHelper
     */
    protected $helper;

    public function __construct() {
        $this->helper = new ConfigHelper();
    }

    public function testFlattenConfig() {
        $configTree = array(
            "section1" => array(
                "type1" => 1,
                "type2" => array(
                    "subtype1" => 'value',
                    "subtype2" => 'value',
                ),
                "type3" => "testValue",
            ),
            "section2" => array(
                "type1" => 1,
            ),
        );

        $configList = $this->helper->flattenConfig($configTree);

        $expected = array(
            'section1.type1' => 1,
            'section1.type2.subtype1' => 'value',
            'section1.type2.subtype2' => 'value',
            'section1.type3' => 'testValue',
            'section2.type1' => 1,
        );

        $this->assertEquals($expected, $configList);
    }

}