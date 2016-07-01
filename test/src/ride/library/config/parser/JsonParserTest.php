<?php

namespace ride\library\config\parser;

class JsonParserTest extends AbstractParserTest {

    protected function getParser() {
        return new JsonParser();
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
'{
    "myKey": "value"
}'
            ),
        );
    }

    public function providerParseFromPhp() {
        return array(
            array(
array(),
'[]'
            ),
            array(
array(
    'myKey' => 'value'
),
'{
    "myKey": "value"
}'
            ),
        );
    }

}
