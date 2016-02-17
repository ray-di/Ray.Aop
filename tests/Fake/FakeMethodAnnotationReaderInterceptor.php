<?php

namespace Ray\Aop;

class FakeMethodAnnotationReaderInterceptor implements MethodInterceptor
{
    public static $classAnnotations;
    public static $classAnnotation;
    public static $methodAnnotations;
    public static $methodAnnotation;

    public function invoke(MethodInvocation $invocation)
    {
        self::$methodAnnotations = $invocation->getMethod()->getAnnotations();
        self::$methodAnnotation = $invocation->getMethod()->getAnnotation(FakeMarker::class);
        self::$classAnnotations = $invocation->getMethod()->getDeclaringClass()->getAnnotations();
        self::$classAnnotation = $invocation->getMethod()->getDeclaringClass()->getAnnotation(FakeClassAnnotation::class);

        return $invocation->proceed();
    }
}
