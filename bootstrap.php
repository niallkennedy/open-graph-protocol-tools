<?php
/**
 * Open Graph Protocol Tools
 *
 * This is the phpunit bootstrap file which loads the composer autoloader.
 * It is also used to load the composer autoloader for the compatibility layer. 
 *
 * @package open-graph-protocol-tools
 * @author Niall Kennedy <niall@niallkennedy.com>
 * @version 2.0
 * @copyright Public Domain
 */

function includeIfExists($file)
{
    if (file_exists($file)) {
        return include $file;
    }
}

if ((!$loader = includeIfExists(__DIR__.'/vendor/autoload.php')) && (!$loader = includeIfExists(__DIR__.'/../../../../autoload.php'))) {
    die('Composer autoloader not found. Please install composer: http://getcomposer.org');
}