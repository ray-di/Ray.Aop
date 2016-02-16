<?php

namespace Ray\Aop;

class FakeMethodAnnotationReaderInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $methodAnnotations = $invocation->getMethod()->getAnnotations();
        $methodAnnotation = $invocation->getMethod()->getAnnotation(FakeMarker::class);

        return [$methodAnnotations, $methodAnnotation];
    }
}
