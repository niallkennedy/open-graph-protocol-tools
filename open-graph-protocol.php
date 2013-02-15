<?php
/**
 * Open Graph Protocol Tools
 *
 * THIS FILE IS DEPRECATED: Please configure your autoloader to load NiallKennedy\OpenGraphProtocolTools
 * and add 'use' statements as required.
 *
 * NOTE: This file isn't PSR-0 compliant (as it is not scoped within a vendor namespace) but is
 * provided for backward compatibility only.
 *
 * @package open-graph-protocol-tools
 * @author Niall Kennedy <niall@niallkennedy.com>
 * @version 2.0
 * @copyright Public Domain
 */

/* The following line is an intentional PSR-1 violation */
trigger_error('Please configure NiallKennedy\\OpenGraphProtocolTools with your autoloader', E_USER_DEPRECATED);
require_once dirname(__FILE__) . '/bootstrap.php';

\NiallKennedy\OpenGraphProtocolTools\Legacy\BackwardCompatibility::createProxyClasses();
