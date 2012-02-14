<?php
/**
 * Ray
 *
 * @package Ray.Di
 * @license http://opensource.org/licenses/bsd-license.php BSD
 */
namespace Ray\Aop;

use Doctrine\Common\Annotations\Reader;

/**
 * Matcher
 *
 * @package Ray.Di
 *
 */
class Matcher
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
     * Constructor
     * s
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Any match
     *
     * @return Ray\Di\Matcher
     */
    public function any()
    {
        $this->method = __FUNCTION__;
        $this->args = null;
        return $this;
    }

    /**
     * Return match(true)
     *
     * @return Ray\Di\Matcher
     */
    public function isAny($class, $target) {
        return true;
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
        $this->method = __FUNCTION__;
        $this->args = $annotationName;
        return $this;
    }

    /**
     * Return match
     *
     * @param string  $class
     * @param string  $target
     * @param array   $annotationName
     *
     * @return boolean|\Ray\Aop\Matched
     */
    private function isAnnotatedWith($class, $target, $annotationName) {
        $reader = $this->reader;
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
    }

    /**
     * Return subclass matche result
     *
     * @param string $superClass
     *
     * @return bool
     */
    public function subclassesOf($superClass)
    {
        $this->method = __FUNCTION__;
        $this->args = $superClass;
        return $this;
    }

    /**
     * Return subclass match.
     *
     * @param string $class
     * @param string $target
     * @param string $superClass
     * @throws \RuntimeException
     */
    private function isSubclassesOf($class, $target, $superClass)
    {
        if ($target === self::TARGET_METHOD) {
            throw new \RuntimeException($class);
        }
        try {
            return (new \ReflectionClass($class))->isSubclassOf($superClass);
        } catch (\Exception $e) {
            return false;
        }
    }

    public function __invoke($class, $target)
    {
        $args = [$class, $target];
        array_push($args, $this->args);
        $matchd = call_user_func_array([$this, 'is' . $this->method], $args);
        return $matchd;
    }

}