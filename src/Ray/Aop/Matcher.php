<?php
/**
 * This file is part of the Ray.Aop package
 *
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Doctrine\Common\Annotations\Reader;
use Ray\Aop\Exception\InvalidAnnotation;
use Ray\Aop\Exception\InvalidArgument as InvalidArgumentException;
use ReflectionClass;

class Matcher extends AbstractMatcher implements Matchable
{
    /**
     * Annotation reader
     *
     * @var Reader
     */
    private $reader;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * {@inheritdoc}
     */
    public function any()
    {
        $this->method = __FUNCTION__;
        $this->args = null;

        return clone $this;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function subclassesOf($superClass)
    {
        $this->method = __FUNCTION__;
        $this->args = $superClass;

        return clone $this;
    }

    /**
     * {@inheritdoc}
     */
    public function startWith($prefix)
    {
        $this->method = __FUNCTION__;
        $this->args = $prefix;

        return clone $this;
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
        $this->args = [$matcherA, $matcherB];

        return clone $this;
    }

    /**
     * {@inheritdoc}
     */
    public function logicalAnd(Matchable $matcherA, Matchable $matcherB)
    {
        $this->method = __FUNCTION__;
        $this->args = [$matcherA, $matcherB];

        return clone $this;
    }

    /**
     * {@inheritdoc}
     */
    public function logicalXor(Matchable $matcherA, Matchable $matcherB)
    {
        $this->method = __FUNCTION__;
        $this->args = [$matcherA, $matcherB];

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
     * @param string $name   class or method name
     * @param bool   $target self::TARGET_CLASS | self::TARGET_METHOD
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function isAny($name, $target)
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
     * Return is annotated with
     *
     * Return Match object if annotate bindings, which containing multiple results.
     * Otherwise return bool.
     *
     * @param string $class
     * @param bool   $target self::TARGET_CLASS | self::TARGET_METHOD
     * @param string $annotationName
     *
     * @return bool | Matched[]
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    protected function isAnnotatedWith($class, $target, $annotationName)
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
     */
    protected function isStartWith($name, $target, $startWith)
    {
        unset($target);
        $result = (strpos($name, $startWith) === 0) ? true : false;

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
        $isOr = $matcherA($name, $target) || $matcherB($name, $target);

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
        $isOr = $matcherA($name, $target) && $matcherB($name, $target);

        return $isOr;
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
        $isOr = ($matcherA($name, $target) xor $matcherB($name, $target));

        return $isOr;
    }

    /**
     * Return logical not matching result
     *
     * @param string    $name
     * @param bool      $target
     * @param Matchable $matcherA
     * @param Matchable $matcherB
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    protected function isLogicalNot($name, $target, Matchable $matcher)
    {
        $isOr = !($matcher($name, $target));

        return $isOr;
    }
}
