<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @package Ray.Aop
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Doctrine\Common\Annotations\Reader;
use Ray\Aop\Exception\InvalidArgument as InvalidArgumentException;
use Ray\Aop\Exception\InvalidAnnotation;
use ReflectionClass;

/**
 * Matcher
 *
 * @package Ray.Aop
 */
/** @noinspection PhpDocMissingReturnTagInspection */
class Matcher implements Matchable
{
    /**
     * Match CLASS
     *
     * @var bool
     */
    const TARGET_CLASS = true;

    /**
     * Match Method
     *
     * @var bool
     */
    const TARGET_METHOD = false;

    /**
     * Annotation reader
     *
     * @var Reader
     */
    private $reader;

    /**
     * Lazy match method
     *
     * @var string
     */
    private $method;

    /**
     * Lazy match args
     *
     * @var array
     */
    private $args;

    /**
     * Constructor
     *
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Return is annotate bindings
     *
     * @return boolean
     */
    public function isAnnotateBinding()
    {
        $isAnnotateBinding = $this->method === 'annotatedWith';

        return $isAnnotateBinding;
    }

    /**
     * Any match
     *
     * @return Matcher
     */
    public function any()
    {
        $this->method = __FUNCTION__;
        $this->args = null;

        return clone $this;
    }

    /**
     * Match binding annotation
     *
     * @param string $annotationName
     *
     * @return Matcher
     * @throws InvalidAnnotation
     */
    public function annotatedWith($annotationName)
    {
        if (!class_exists($annotationName)) {
            throw new InvalidAnnotation($annotationName);
        }
        $this->method = __FUNCTION__;
        $this->args = $annotationName;

        return clone $this;
    }

    /**
     * Return subclass matched result
     *
     * @param string $superClass
     *
     * @return Matcher
     */
    public function subclassesOf($superClass)
    {
        $this->method = __FUNCTION__;
        $this->args = $superClass;

        return clone $this;
    }

    /**
     * Return prefix match result
     *
     * @param string $prefix
     *
     * @return Matcher
     */
    public function startWith($prefix)
    {
        $this->method = __FUNCTION__;
        $this->args = $prefix;

        return clone $this;
    }

    /**
     * Return match(true)
     *
     * @param string $name   class or method name
     * @param bool   $target self::TARGET_CLASS | self::TARGET_METHOD
     *
     * @return Matcher
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function isAny($name, $target)
    {
        if ($target === self::TARGET_CLASS) {
            return true;
        }
        if (substr($name, 0, 2) === '__') {
            return false;
        }
        if (in_array(
            $name,
            [
                'offsetExists',
                'offsetGet',
                'offsetSet',
                'offsetUnset',
                'append',
                'getArrayCopy',
                'count',
                'getFlags',
                'setFlags',
                'asort',
                'ksort',
                'uasort',
                'uksort',
                'natsort',
                'natcasesort',
                'unserialize',
                'serialize',
                'getIterator',
                'exchangeArray',
                'setIteratorClass',
                'getIterator',
                'getIteratorClass'
            ]
        )
        ) {
            return false;
        }

        return true;
    }

    /**
     * Return match result
     *
     * Return Match object if annotate bindings, which containing multiple results.
     * Otherwise return bool.
     *
     * @param string $class
     * @param bool   $target         self::TARGET_CLASS | self::TARGET_METHOD
     * @param string $annotationName
     *
     * @return bool | Matched[]
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function isAnnotatedWith($class, $target, $annotationName)
    {
        $reader = $this->reader;
        if ($target === self::TARGET_CLASS) {
            $annotation = $reader->getClassAnnotation(new ReflectionClass($class), $annotationName);
            $hasAnnotation = $annotation ? true : false;

            return $hasAnnotation;
        }
        $methods = (new ReflectionClass($class))->getMethods();
        $result = [];
        foreach ($methods as $method) {
            new $annotationName;
            $annotation = $reader->getMethodAnnotation($method, $annotationName);
            if ($annotation) {
                $matched = new Matched;
                $matched->methodName = $method->name;
                $matched->annotation = $annotation;
                $result[] = $matched;
            }
        }

        return $result;
    }

    /**
     * Return subclass match.
     *
     * @param string $class
     * @param bool   $target     self::TARGET_CLASS | self::TARGET_METHOD
     * @param string $superClass
     *
     * @return bool
     * @throws InvalidArgumentException
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function isSubclassesOf($class, $target, $superClass)
    {
        if ($target === self::TARGET_METHOD) {
            throw new InvalidArgumentException($class);
        }
        try {
            $isSubClass = (new ReflectionClass($class))->isSubclassOf($superClass);
            if ($isSubClass === false) {
                $isSubClass = ($class === $superClass);
            }

            return $isSubClass;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Return prefix match
     *
     * @param string $name
     * @param string $target
     * @param string $startWith
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @noinspection PhpUnusedPrivateMethodInspection
     */
    private function isStartWith($name, $target, $startWith)
    {
        unset($target);
        $result = (strpos($name, $startWith) === 0) ? true : false;

        return $result;
    }

    /**
     * Return match result
     *
     * @param string $class
     * @param bool   $target self::TARGET_CLASS | self::TARGET_METHOD
     *
     * @return bool | array [$matcher, method]
     */
    public function __invoke($class, $target)
    {
        $args = [$class, $target];
        array_push($args, $this->args);
        $method = 'is' . $this->method;
        $matched = call_user_func_array([$this, $method], $args);

        return $matched;
    }

    /**
     * __toString magic method
     *
     * @return string
     */
    public function __toString()
    {
        $result = $this->method . ':' . json_encode($this->args);

        return $result;
    }
}
