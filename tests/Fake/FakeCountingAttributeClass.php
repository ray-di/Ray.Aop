<?php

declare(strict_types=1);

namespace Ray\Aop;

class FakeCountingAttributeClass
{
    #[FakeCountingAttribute]
    public function run(): void
    {
    }
}
