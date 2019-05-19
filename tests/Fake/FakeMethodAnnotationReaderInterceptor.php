<?php
namespace Ray\Aop;

use Ray\Aop\Annotation\FakeMarker;

class FakeMethodAnnotationReaderInterceptor implements MethodInterceptor
{
    public static $classAnnotations;
    public static $classAnnotation;
    public static $methodAnnotations;
    public static $methodAnnotation;

    public function invoke(MethodInvocation $invocation)
    {
        /** @var \Ray\Aop\ReflectionMethod $method */
        $method = $invocation->getMethod();
        self::$methodAnnotations = $method->getAnnotations();
        self::$methodAnnotation = $method->getAnnotation(FakeMarker::class);
        self::$classAnnotations = $method->getDeclaringClass()->getAnnotations();
        self::$classAnnotation = $method->getDeclaringClass()->getAnnotation(FakeClassAnnotation::class);

        return $invocation->proceed();
    }
}
