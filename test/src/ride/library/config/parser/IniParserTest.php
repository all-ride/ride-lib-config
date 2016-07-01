<?php

namespace ride\library\config\parser;

class IniParserTest extends AbstractParserTest {

    protected function getParser() {
        return new IniParser();
    }

    public function providerParseToPhp() {
        return array(
            array(
array(),
''
            ),
            array(
array(
    'myKey' => 'value'
),
'myKey = value
'
            ),
            array(
array(
    'section.myKey' => 'value'
),
'section.myKey = value
'
            ),
            array(
array(
    'section' => array(
        'myKey' => 'value',
    ),
),
'[section]
myKey = value
'
            ),
        );
    }

    public function providerParseFromPhp() {
        return array(
            array(
array(),
''
            ),
            array(
array(
    'myKey' => 'value'
),
'myKey = value
'
            ),
            array(
array(
    'section.myKey' => 'value'
),
'section.myKey = value
'
            ),
            array(
array(
    'section' => array(
        'myKey' => 'value',
    ),
),
'section.myKey = value
'
            ),
        );
    }

}
