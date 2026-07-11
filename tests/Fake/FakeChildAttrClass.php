<?php

declare(strict_types=1);

namespace Ray\Aop;

class FakeChildAttrClass
{
    #[FakeChildAttr]
    public function run(): string
    {
        return 'ok';
    }
}
