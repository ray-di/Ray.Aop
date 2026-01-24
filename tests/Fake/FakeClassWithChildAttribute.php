<?php

declare(strict_types=1);

namespace Ray\Aop;

#[FakeChildAttribute]
class FakeClassWithChildAttribute
{
    #[FakeChildAttribute]
    public function annotatedMethod(): void
    {
    }
}
