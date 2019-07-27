<?php

namespace {
    use Ray\Aop\WeavedInterface;
    use Ray\Aop\ReflectiveMethodInvocation as Invocation;
    use Ray\Aop\FakeNum;
    /** doc comment of FakeMock */
    class FakeGlobalNamespaced_MXKCAJQ extends \FakeGlobalNamespaced implements WeavedInterface
    {
        public $bind;
        public $bindings = [];
        public $methodAnnotations = 'a:0:{}';
        public $classAnnotations = 'a:0:{}';
        private $isAspect = true;
        /**
         * doc comment of returnSame
         */
        public function returnSame($a)
        {
            if (!$this->isAspect) {
                $this->isAspect = true;
                return parent::returnSame($a);
            }
            $this->isAspect = false;
            $result = (new Invocation($this, __FUNCTION__, [$a], $this->bindings[__FUNCTION__]))->proceed();
            $this->isAspect = true;
            return $result;
        }
    }
}
