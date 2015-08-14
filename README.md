# Ride: Configuration Library

Configuration library of the PHP Ride framework.

## Code Sample

Check this code sample to see the possibilities of this library:

~~~php
<?php

use ride\library\config\io\ParserConfigIO;
use ride\library\config\parser\JsonParser;
use ride\library\config\ConfigHelper;
use ride\library\config\GenericConfig;
use ride\library\system\file\browser\GenericFileBrowser;
use ride\library\system\System;

// Initialize the system abstraction.
$system = new System();

// We need a file browser to lookup files.
// This instance will only have an application directory, you can add more include directories if needed.
// The application directory is required to write parameters.
$applicationDirectory = $system->getFileSystem()->getFile(__DIR__);

$fileBrowser = new GenericFileBrowser();
$fileBrowser->setApplicationDirectory($applicationDirectory);

// Create the config helper, our parser and the config itself will use this.
$configHelper = new ConfigHelper();

// Let's use the JSON format...
$parser = new JsonParser();

// Now we create a config input/output implementation for all config/parameters.json files found in the file browser
$configIO = new PerserConfigIO($fileBrowser, $configHelper, $parser, 'parameters.json', 'config');

// As final step, we create the config instance which is the main access point to the configuration parameters.
$config = new GenericConfig($configIO, $configHelper);

// You can get a value, optionally with a default.
$name = $config->get('system.name'); // null, not set
$name = $config->get('system.name', 'Ride'); // 'Ride' as default value

// You can set a value, which is automatically written to the IO.
$config->set('system.name', 'My System');
$config->set('system.secret', 'ABCDEF');

// You can get parameters which are not leafs of the configuration tree
$parameters = $config->get('system'); 
// [
//  'name' => 'My System', 
//  'secret' => 'ABCDEF'
// ]

// you can use the config helper to flatten a structure
$config->set('system.directory.cache', 'cache');
$config->set('system.directory.template', 'templates');

$parameters = $config->get('system'); // ['name' => 'My System', 'secret' => 'ABCDEF', 'directory' => [
// [
//  'name' => 'My System', 
//  'secret' => 'ABCDEF'
//  'directory' => [
//    'cache' => 'cache',    
//    'template' => 'templates',    
//  ]
// ]

$parameters = $configHelper->flattenConfig($parameters);
// [
//  'name' => 'My System', 
//  'secret' => 'ABCDEF'
//  'directory.cache' => 'cache',    
//  'directory.template' => 'templates',    
// ]

## Limitations

* Keys should have at least 2 tokens. 
* You cannot have a value for a key which has subkeys. 
This means, if you have a value for a key called _system.directory.cache_, you can not have a value for _system.directory_. 
