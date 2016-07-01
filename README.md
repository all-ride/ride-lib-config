# Ride: Configuration Library

Configuration library of the PHP Ride framework.

## Config

The _Config_ interface defines a configuration data container used to get and set parameters.
A generic implementation is provided.

## ConfigIO

To read and write the configuration from the data source, the _ConfigIO_ interface is used.
Out of the box, you can create a _ParserConfigIO_ with a parser of your choice wrapped around the file browser of _ride-lib-system_.
You can wrap any IO around the _CachedConfigIO_ to improve performance.

## Parser

The _Parser_ interface is used to read and write different file formats.
An ini and json implementation are provided.

## Code Sample

Check this code sample to see the possibilities of this library:

```php
<?php

use ride\library\config\io\CachedConfigIO;
use ride\library\config\io\ParserConfigIO;
use ride\library\config\parser\JsonParser;
use ride\library\config\ConfigHelper;
use ride\library\config\GenericConfig;
use ride\library\system\file\browser\FileBrowser;

function foo(FileBrowser $fileBrowser) {
    // Create the config helper, our IO and the config itself will use this.
    $configHelper = new ConfigHelper();

    // Let's use the JSON format...
    $parser = new JsonParser();

    // Now we create a config input/output implementation for all config/parameters.json files found in the file browser
    $configIO = new ParserConfigIO($fileBrowser, $configHelper, $parser, 'parameters.json', 'config');

    // optionally, you can wrap it around a cached version
    $cacheFile = $fileBrowser->getFileSystem()->getFile(__DIR__ . '/config.cache');
    $configIO = new CachedConfigIO($configIO, $cacheFile);

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

    $parameters = $config->get('system');
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
}
```

## Limitations

You cannot have a value for a key which has subkeys.
This means, if you have a value for a key called _system.directory.cache_, you can not have a value for _system.directory_. 
