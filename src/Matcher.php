<?php
/**
 * Ray
 *
 * This file is taken from Aura Project and modified. (namespace only)
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Doctrine\Common\Annotations\Reader;

/**
 * Matcher
 *
 * @package Aura.Di
 *
 */
class Matcher
{
    const ANY = true;

    const TARGET_CLASS = true;
    const TARGET_METHOD = false;

    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Invokes the closure to create the instance.
     *
     * @return object The object created by the closure.
     *
     */
    public function __invoke($arg, $target = self::TARGET_CLASS)
    {
        $callable = $this->callable;
        return $callable($arg, $target);
    }

    /**
     * Any match
     *
     * @return Ray\Di\Matcher
     */
    public function any()
    {
        return function(){
            return self::ANY;
        };
    }

    /**
     * Match binding annotation
     *
     * @param string $annotationName
     *
     * @return \Ray\Di\Matcher
     */
    public function annotatedWith($annotationName)
    {
        $reader = $this->reader;

        $this->callable = function($class, $target) use ($annotationName, $reader) {
            if ($target === self::TARGET_CLASS) {
                $annotation = $reader->getClassAnnotation(new \ReflectionClass($class), $annotationName);
                $hasAnnotation = $annotation ? true : false;
                return $hasAnnotation;
            }
            $methods = (new \ReflectionClass($class))->getMethods();
            $result = [];
            foreach ($methods as $method) {
                $annotation = $reader->getMethodAnnotation($method, $annotationName);
                if ($annotation) {
                    $matched = new Matched;
                    $matched->methodName = $method->name;
                    $matched->annotation = $annotation;
                    $result[] = $matched;
                }
            }
            return $result;
        };
        return $this;
    }

    public function call(Callable $callable)
    {
        return function ($name) use ($callable){
            return $callable($name);
        };
    }
}
