<?php

declare(strict_types=1);

namespace Ray\Aop;

// Deliberately not PSR-12: the class body brace is on the declaration line.
// PSR-12 puts it on the next line, which is why this shape went unnoticed.
class FakeSameLineBraceClass implements FakeNullInterface { public function returnSame(int $a): int { return $a; } }
