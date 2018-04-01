<?php

namespace ride\library\config;

use PHPUnit\Framework\TestCase;

class ConfigHelperTest extends TestCase {

    /**
     * @var ride\library\config\ConfigHelper
     */
    protected $helper;

    public function setUp() {
        $this->helper = new ConfigHelper();
    }

    /**
     * @dataProvider providerSetValue
     */
    public function testSetValue($expected, $data, $key, $value) {
        $this->helper->setValue($data, $key, $value);

        $this->assertEquals($expected, $data);
    }

    public function providerSetValue() {
        return array(
            array(
                array('key' => 'value'),
                array(),
                'key',
                'value',
            ),
            array(
                array('section1' => array('type1' => 1)),
                array(),
                'section1.type1',
                1,
            ),
            array(
                array('section1' => array('type1' => 2)),
                array('section1' => array('type1' => 1)),
                'section1.type1',
                2,
            ),
            array(
                array('section1' => array('type1' => 1, 'type2' => 2)),
                array('section1' => array('type1' => 1)),
                'section1.type2',
                2,
            ),
            array(
                array('section1' => array('type1' => array('subtype1' => 1))),
                array('section1' => array('type1' => 1)),
                'section1.type1.subtype1',
                1,
            ),
        );
    }

    /**
     * @dataProvider providerSetValueThrowsConfigExceptionWhenInvalidValueProvided
     * @expectedException ride\library\config\exception\ConfigException
     */
    public function testSetValueThrowsConfigExceptionWhenInvalidKeyProvided($key) {
        $data = array();
        $value = 'test';

        $this->helper->setValue($data, $key, $value);
    }

    public function providerSetValueThrowsConfigExceptionWhenInvalidValueProvided() {
        return array(
            array(null),
            array(false),
            array(array()),
            array($this),
        );
    }

    /**
     * @dataProvider providerFlattenConfig
     */
    public function testFlattenConfig($expected, $tree) {
        $result = $this->helper->flattenConfig($tree);

        $this->assertEquals($expected, $result);
    }

    public function providerFlattenConfig() {
        return array(
            array(
                array(
                    'section1.type1' => 1,
                    'section1.type2.subtype1' => 'value',
                    'section1.type2.subtype2' => 'value',
                    'section1.type3' => 'testValue',
                    'section2.type1' => 1,
                ),
                array(
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
                )
            ),
        );
    }

}
