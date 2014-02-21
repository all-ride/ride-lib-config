<?php

namespace ride\library\config;

use \PHPUnit_Framework_TestCase;

class GenericConfigTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Config
     */
    private $config;

    private $ioMock;

    private $key;

    private $value;

    private $mockFile;

    private $mockResult;

    private $mockValue;

    protected function setUp() {
        $this->key = 'file.section';
        $this->value = array(
            'key' => array(
                '1' => 'value',
                '2' => 'value',
            ),
        );
        $this->mockFile = 'file';
        $this->mockResult = array(
            'section' => array(
                'key' => array(
                    '1' => 'value',
                    '2' => 'value',
                ),
            ),
        );
        $this->mockValue = 'test';

        $this->ioMock = $this->getMock('ride\\library\\config\\io\\ConfigIO');
        $this->config = new GenericConfig($this->ioMock);
    }

    protected function setMockReadExpectation($expectation) {
        $this->ioMock
               ->expects($expectation)
               ->method('get')
               ->with($this->equalTo($this->mockFile))
               ->will($this->returnValue($this->mockResult));
    }

    protected function setMockWriteExpectation($expectation) {
        $this->ioMock
               ->expects($expectation)
               ->method('set')
               ->with($this->equalTo($this->key), $this->equalTo($this->mockValue));
    }

    protected function setMockALlExpectation($expectation) {
        $this->ioMock
               ->expects($expectation)
               ->method('getAll')
               ->will($this->returnValue($this->mockResult));
    }

    public function testGetAll() {
        $this->setMockAllExpectation($this->once());

        $value = $this->config->getAll();

        $this->assertEquals($this->mockResult, $value);
    }

    public function testGetValue() {
        $this->setMockReadExpectation($this->once());
        $value = $this->config->get($this->key);
        $this->assertEquals($this->value, $value);
        $value = $this->config->get('file.section.key.1');
        $this->assertEquals('value', $value);
    }

    public function testGetValueTwiceReadsFileOnlyOnce() {
        $this->setMockReadExpectation($this->once());
        $this->config->get($this->key);
        $this->config->get($this->key);
    }

    /**
     * @expectedException ride\library\config\exception\ConfigException
     */
    public function testGetValueThrowsExceptionWhenKeyIsEmpty() {
        $this->config->get('');
    }

    public function testGetDefaultValueWhenKeyDoesNotExists() {
        $this->setMockReadExpectation($this->once());
        $value = $this->config->get('file.unknown', $this->value);
        $this->assertEquals($this->value, $value);
    }

    public function testGetDefaultValueWhenSectionIsRequestedButDoesNotExists() {
        $this->ioMock
            ->expects($this->once())
            ->method('get')
            ->with($this->equalTo('test'))
            ->will($this->returnValue(array()));

        $value = $this->config->get('test', $this->value);

        $this->assertEquals($this->value, $value);
    }

    public function testSetValue() {
        $this->setMockWriteExpectation($this->once());
        $this->config->set($this->key, $this->mockValue);
        $value = $this->config->get($this->key);
        $this->assertEquals($this->mockValue, $value);
    }

    public function testSetValueNullRemovesKey() {
        $default = 'default';
        $this->mockValue = null;

        $this->setMockWriteExpectation($this->once());
        $this->config->set($this->key, $this->mockValue);

        $value = $this->config->get($this->key, $default);
        $this->assertEquals($default, $value);
    }

}