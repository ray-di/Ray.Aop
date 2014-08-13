<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\Common\Annotations\Reader;
use Ray\Aop\Exception\InvalidAnnotation;
use Ray\Aop\Exception\InvalidArgument as InvalidArgumentException;
use ReflectionClass;

class Matcher extends AbstractMatcher implements Matchable
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
    public function __construct(Reader $reader = null)
    {
        if (is_null(self::$annotationReader)) {
            self::$annotationReader = $reader ?: new AnnotationReader;
        }
        $this->reader = self::$annotationReader;
    }

    /**
     * @param Reader $reader
     */
    public static function setAnnotationReader(Reader $reader)
    {
        self::$annotationReader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function any()
    {
        return $this->createMatcher(__FUNCTION__, null);
    }

    /**
     * {@inheritdoc}
     */
    public function annotatedWith($annotationName)
    {
        if (!class_exists($annotationName)) {
            throw new InvalidAnnotation($annotationName);
        }

        return $this->createMatcher(__FUNCTION__, $annotationName);
    }

    /**
     * {@inheritdoc}
     */
    public function subclassesOf($superClass)
    {
        return $this->createMatcher(__FUNCTION__, $superClass);
    }

    /**
     * @deprecated
     */
    public function startWith($prefix)
    {
        return $this->startsWith($prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function startsWith($prefix)
    {
        return $this->createMatcher(__FUNCTION__, $prefix);
    }

    /**
     * Return isAnnotateBinding
     *
     * @return bool
     */
    public function isAnnotateBinding()
    {
        $isAnnotateBinding = $this->method === 'annotatedWith';

        return $isAnnotateBinding;
    }

    /**
     * {@inheritdoc}
     */
    public function logicalOr(Matchable $matcherA, Matchable $matcherB)
    {
        $this->method = __FUNCTION__;
        $this->args = func_get_args();

        return clone $this;
    }

    /**
     * {@inheritdoc}
     */
    public function logicalAnd(Matchable $matcherA, Matchable $matcherB)
    {
        $this->method = __FUNCTION__;
        $this->args = func_get_args();

        return clone $this;
    }

    /**
     * {@inheritdoc}
     */
    public function logicalXor(Matchable $matcherA, Matchable $matcherB)
    {
        $this->method = __FUNCTION__;
        $this->args = func_get_args();

        return clone $this;
    }

    /**
     * {@inheritdoc}
     */
    public function logicalNot(Matchable $matcher)
    {
        $this->method = __FUNCTION__;
        $this->args = $matcher;

        return clone $this;
    }

    /**
     * Return isAny
     *
     * @param mixed $name string(class name) | ReflectionMethod
     * @param bool  $target self::TARGET_CLASS | self::TARGET_METHOD
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function isAny($name, $target)
    {
        if ($name instanceof \ReflectionMethod) {
            $name = $name->name;
        }
        if ($target === self::TARGET_CLASS) {
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
     * @param mixed  $class string(class name) | ReflectionMethod
     * @param bool   $target self::TARGET_CLASS | self::TARGET_METHOD
     * @param string $annotationName
     *
     * @return bool | Matched[]
     * @throws InvalidArgumentException
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    protected function isAnnotatedWith($class, $target, $annotationName)
    {
        if ($class instanceof \ReflectionMethod) {
            return $this->isAnnotatedMethod($target, $class, $annotationName);
        }
        if ($target !== self::TARGET_CLASS) {
            return $this->setAnnotations($class, $annotationName);
        }
        $annotation = $this->reader->getClassAnnotation(new ReflectionClass($class), $annotationName);
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
        if ($target === self::TARGET_CLASS) {
            throw new InvalidArgumentException($method->name);
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
     * @param bool   $target self::TARGET_CLASS | self::TARGET_METHOD
     * @param string $superClass
     *
     * @return bool
     * @throws InvalidArgumentException
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    protected function isSubclassesOf($class, $target, $superClass)
    {
        if ($class instanceof \ReflectionMethod) {
            throw new InvalidArgumentException($class->name);
        }
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
     * @param string $startsWith
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    protected function isStartsWith($name, $target, $startsWith)
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
     * @param string    $name
     * @param bool      $target
     * @param Matchable $matcherA
     * @param Matchable $matcherB
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    protected function isLogicalOr($name, $target, Matchable $matcherA, Matchable $matcherB)
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
     * @param string    $name
     * @param bool      $target
     * @param Matchable $matcherA
     * @param Matchable $matcherB
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    protected function isLogicalAnd($name, $target, Matchable $matcherA, Matchable $matcherB)
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
     * @param string    $name
     * @param bool      $target
     * @param Matchable $matcherA
     * @param Matchable $matcherB
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    protected function isLogicalXor($name, $target, Matchable $matcherA, Matchable $matcherB)
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
     * @param string    $name
     * @param bool      $target
     * @param Matchable $matcher
     *
     * @return bool
     */
    protected function isLogicalNot($name, $target, Matchable $matcher)
    {
        $isNot = !($matcher($name, $target));

        return $isNot;
    }
}
