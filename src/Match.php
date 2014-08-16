<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use ReflectionClass;
use Ray\Aop\Exception\InvalidArgument;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Annotations\AnnotationReader;

class Match
{
    /**
     * @var array
     */
    protected $builtinMethods = [
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
    ];

    /**
     * Annotation reader
     *
     * @var Reader
     */
    private static $annotationReader;

    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @param Reader $reader
     */
    public static function setAnnotationReader(Reader $reader)
    {
        self::$annotationReader = $reader;
    }

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader = null)
    {
        if (is_null(self::$annotationReader)) {
            self::$annotationReader = $reader ?: new AnnotationReader;
        }
        $this->reader = self::$annotationReader;
    }

    /**
     * Return isAny
     *
     * @param mixed $name string(class name) | ReflectionMethod
     * @param bool  $target AbstractMatcher::TARGET_CLASS | AbstractMatcher::TARGET_METHOD
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isAny($name, $target)
    {
        if ($name instanceof \ReflectionMethod) {
            $name = $name->name;
        }
        if ($target === AbstractMatcher::TARGET_CLASS) {
            return true;
        }
        if (substr($name, 0, 2) === '__') {
            return false;
        }
        return in_array($name, $this->builtinMethods) ? false : true;
    }

    /**
     * Return is annotated with
     *
     * Return Match object if annotate bindings, which containing multiple results.
     * Otherwise return bool.
     *
     * @param mixed  $name string(class name) | ReflectionMethod
     * @param bool   $target AbstractMatcher::TARGET_CLASS | AbstractMatcher::TARGET_METHOD
     * @param string $annotationName
     *
     * @return bool | Matched[]
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    public function isAnnotatedWith($name, $target, $annotationName)
    {
        if ($name instanceof \ReflectionMethod) {
            return $this->isAnnotatedMethod($target, $name, $annotationName);
        }
        if ($target !== AbstractMatcher::TARGET_CLASS) {
            return $this->setAnnotations($name, $annotationName);
        }
        $annotation = $this->reader->getClassAnnotation(new ReflectionClass($name), $annotationName);
        $hasAnnotation = $annotation ? true : false;

        return $hasAnnotation;
    }

    /**
     * @param bool              $target
     * @param \ReflectionMethod $method
     * @param string            $annotationName
     *
     * @return array|bool
     * @throws Exception\InvalidArgument
     */
    private function isAnnotatedMethod($target, $method, $annotationName)
    {
        if ($target === AbstractMatcher::TARGET_CLASS) {
            throw new InvalidArgument($method->name);
        }
        new $annotationName;
        $annotation = $this->reader->getMethodAnnotation($method, $annotationName);
        if (! $annotation) {
            return false;
        }
        $matched = new Matched;
        $matched->methodName = $method->name;
        $matched->annotation = $annotation;

        return [$matched];
    }

    /**
     * Set annotations
     *
     * @param string $class
     * @param string $annotationName
     *
     * @return array
     */
    private function setAnnotations($class, $annotationName)
    {
        $methods = (new ReflectionClass($class))->getMethods();
        $result = [];
        foreach ($methods as $method) {
            new $annotationName;
            $annotation = $this->reader->getMethodAnnotation($method, $annotationName);
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
     * Return is subclass of
     *
     * @param string $class
     * @param bool   $target AbstractMatcher::TARGET_CLASS | AbstractMatcher::TARGET_METHOD
     * @param string $superClass
     *
     * @return bool
     * @throws InvalidArgument
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    public function isSubclassesOf($class, $target, $superClass)
    {
        if ($class instanceof \ReflectionMethod) {
            throw new InvalidArgument($class->name);
        }
        if ($target === AbstractMatcher::TARGET_METHOD) {
            throw new InvalidArgument($class);
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
     * @param mixed  $name string (class name) or ReflectionMethod
     * @param string $target
     * @param string $startsWith
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    public function isStartsWith($name, $target, $startsWith)
    {
        unset($target);
        if ($name instanceof \ReflectionMethod) {
            $name = $name->name;
        }
        $result = (strpos($name, $startsWith) === 0) ? true : false;

        return $result;
    }

    /**
     * Return logical or matching result
     *
     * @param mixed     $name
     * @param bool      $target
     * @param Matchable $matcherA
     * @param Matchable $matcherB
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    public function isLogicalOr($name, $target, Matchable $matcherA, Matchable $matcherB)
    {
        // a or b
        $isOr = ($matcherA($name, $target) or $matcherB($name, $target));
        if (func_num_args() <= 4) {
            return $isOr;
        }
        // a or b or c ...
        $args = array_slice(func_get_args(), 4);
        foreach ($args as $arg) {
            $isOr = ($isOr or $arg($name, $target));
        }

        return $isOr;
    }

    /**
     * Return logical and matching result
     *
     * @param mixed     $name
     * @param bool      $target
     * @param Matchable $matcherA
     * @param Matchable $matcherB
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    public function isLogicalAnd($name, $target, Matchable $matcherA, Matchable $matcherB)
    {
        $isAnd = ($matcherA($name, $target) and $matcherB($name, $target));
        if (func_num_args() <= 4) {
            return $isAnd;
        }
        $args = array_slice(func_get_args(), 4);
        foreach ($args as $arg) {
            $isAnd = ($isAnd and $arg($name, $target));
        }

        return $isAnd;
    }

    /**
     * Return logical xor matching result
     *
     * @param mixed     $name
     * @param bool      $target
     * @param Matchable $matcherA
     * @param Matchable $matcherB
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    public function isLogicalXor($name, $target, Matchable $matcherA, Matchable $matcherB)
    {
        $isXor = ($matcherA($name, $target) xor $matcherB($name, $target));
        if (func_num_args() <= 4) {
            return $isXor;
        }
        $args = array_slice(func_get_args(), 4);
        foreach ($args as $arg) {
            $isXor = ($isXor xor $arg($name, $target));
        }

        return $isXor;
    }

    /**
     * Return logical not matching result
     *
     * @param mixed     $name
     * @param bool      $target
     * @param Matchable $matcher
     *
     * @return bool
     */
    public function isLogicalNot($name, $target, Matchable $matcher)
    {
        $isNot = !($matcher($name, $target));

        return $isNot;
    }
}
