<?php

declare(strict_types=1);

namespace Ray\Aop;

#[FakeChildAttribute]
class FakeClassWithChildAttribute
{
    /**
     * Placeholder method annotated with FakeChildAttribute.
     *
     * Exists solely to carry the attribute; the method has no implementation.
     */
    #[FakeChildAttribute]
    public function annotatedMethod(): void
    {
    }
}