<?php

namespace Ray\Aop;

class FakeMethodAnnotationReaderInterceptor implements MethodInterceptor
{
    public static $methodAnnotations;
    public static $methodAnnotation;

    public function invoke(MethodInvocation $invocation)
    {
        self::$methodAnnotations = $invocation->getMethod()->getAnnotations();
        self::$methodAnnotation = $invocation->getMethod()->getAnnotation(FakeMarker::class);
        return $invocation->proceed();
    }
}
