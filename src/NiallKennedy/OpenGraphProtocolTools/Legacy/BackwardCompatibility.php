<?php
/**
 * Open Graph Protocol Tools
 *
 * @package open-graph-protocol-tools
 * @author Niall Kennedy <niall@niallkennedy.com>
 * @version 2.0
 * @copyright Public Domain
 */

namespace NiallKennedy\OpenGraphProtocolTools\Legacy;

use Exception;
use ReflectionClass;

/**
 * Ploxy object to provide backward compatibility
 *
 * @author Loren Osborn <loren.osborn@hautelook.com>
 */
class BackwardCompatibility
{
    const PACKAGE_NAMESPACE = 'NiallKennedy\\OpenGraphProtocolTools';

    private $proxiedObject;
    private static $classCreationChecklist = array();

    private static function getLegacyClassNameMap()
    {
        return array(
            self::PACKAGE_NAMESPACE . '\\Media\\Media'          => 'OpenGraphProtocolMedia',
            self::PACKAGE_NAMESPACE . '\\Media\\Audio'          => 'OpenGraphProtocolAudio',
            self::PACKAGE_NAMESPACE . '\\Media\\VisualMedia'    => 'OpenGraphProtocolVisualMedia',
            self::PACKAGE_NAMESPACE . '\\Media\\Image'          => 'OpenGraphProtocolImage',
            self::PACKAGE_NAMESPACE . '\\Media\\Video'          => 'OpenGraphProtocolVideo',
            self::PACKAGE_NAMESPACE . '\\Objects\\Object'       => 'OpenGraphProtocolObject',
            self::PACKAGE_NAMESPACE . '\\Objects\\Article'      => 'OpenGraphProtocolArticle',
            self::PACKAGE_NAMESPACE . '\\Objects\\Book'         => 'OpenGraphProtocolBook',
            self::PACKAGE_NAMESPACE . '\\Objects\\Profile'      => 'OpenGraphProtocolProfile',
            self::PACKAGE_NAMESPACE . '\\Objects\\Video'        => 'OpenGraphProtocolVideoObject',
            self::PACKAGE_NAMESPACE . '\\Objects\\VideoEpisode' => 'OpenGraphProtocolVideoEpisode',
            self::PACKAGE_NAMESPACE . '\\OpenGraphProtocol'     => 'OpenGraphProtocol'
        );
    }

    protected function __construct($objectToProxy)
    {
        $legacyClassNameMap = self::getLegacyClassNameMap();
        if (!array_key_exists(get_class($objectToProxy), $legacyClassNameMap)) {
            throw new Exception('Internal error: unknown class: ' . get_class($objectToProxy));
        }
        $this->proxiedObject = $objectToProxy;
    }

    public function __call($name, $arguments)
    {
        if (method_exists($this->proxiedObject, $name)) {
            $cleanArgs = self::cleanProxyCallArgs(get_class($this->proxiedObject), $name, $arguments);

            return call_user_func_array(array($this->proxiedObject, $name), $cleanArgs);
        }
        throw new Exception('No such method: ' . $name);
    }

    private static function cleanProxyCallArgs($class, $method, $arguments)
    {
        $result = array();
        foreach ($arguments as $arg) {
            if ($arg instanceof self) {
                $result[] = $arg->proxiedObject;
            } else {
                $result[] = $arg;
            }
        }

        return $result;
    }

    protected static function callStaticInternal($name, $arguments, $className)
    {
        $inflectedName = self::inflectStaticMethodName($name);
        if (method_exists($className, $inflectedName)) {
            $cleanArgs = self::cleanProxyCallArgs($className, $name, $arguments);
            $result = forward_static_call_array(array($className, $inflectedName), $cleanArgs);

            return $result;
        }
        throw new Exception('No such method: ' . $name);
    }

    public static function createProxyClasses()
    {
        $legacyClassNameMap = self::getLegacyClassNameMap();
        foreach ($legacyClassNameMap as $className => $legacyClassName) {
            if (!array_key_exists($className, self::$classCreationChecklist)) {
                self::createProxyClass($className, $legacyClassName, $legacyClassNameMap);
            }
        }
    }

    private static function createProxyClass($className, $legacyClassName, $legacyClassNameMap)
    {
        $reflection = new ReflectionClass($className);
        $parent     = __CLASS__;
        if ($reflection->getParentClass()) {
            $parentClassName = $reflection->getParentClass()->getName();
            if (!array_key_exists($parentClassName, self::$classCreationChecklist)) {
                self::createProxyClass($legacyClassNameMap[$parentClassName], $parentClassName, $legacyClassNameMap);
            }
            $parent = $legacyClassNameMap[$parentClassName];
        }
        $abstract  = $reflection->isAbstract() ? 'abstract' : '';
        $constants = '';
        foreach ($reflection->getConstants() as $constName => $constValue) {
            $constants .= "const {$constName} = " . var_export($constValue, true) . '; ';
        }
        $compatibilityClassSource =
            "{$abstract} class {$legacyClassName} extends {$parent}" .
            '{ ' .
                $constants .
                /* No legacy classes have constructors that take arguments. */
                'public function __construct() ' .
                '{' .
                    __CLASS__ . '::__construct(new ' . $className . '());' .
                '} ' .
                'static public function __callStatic($name, $arguments) ' .
                '{' .
                    'return ' . __CLASS__ . '::callStaticInternal($name, $arguments, \'' . preg_replace('/\\\\/', '\\\\', $className) . '\');' .
                '}' .
            '}';
        eval($compatibilityClassSource);
        self::$classCreationChecklist[$className] = true;
    }

    public static function inflectStaticMethodName($name)
    {
        return lcfirst(implode('', array_map('ucfirst', explode('_', $name))));
    }
}
