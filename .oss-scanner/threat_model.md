# Threat model

## What this project does and where untrusted input enters

Ray.Aop is a PHP library, not a service; the application that uses it decides what is woven. It takes three
inputs and turns the first into generated PHP source:

- a class name and that class's source file, from which `AopCode` builds a subclass;
- a `Bind`: the method names to intercept and the interceptor class names to call;
- a `classDir`, where the generated subclass is written and later `require`d (`Compiler`, `Weaver`).

Treat all three as untrusted. Callers commonly pass class names that came from a router or a container
definition, so an application can reach `Compiler::compile()` with a name derived from request data, and a cache
directory can be a shared or predictable path such as a temp directory.

## Components that matter most / least

- `src/AopCode.php` — the code generator. It rewrites the original file's text, so a text-level mismatch becomes
  generated code that executes. Most valuable target.
- `src/Compiler.php`, `src/AopPostfixClassName.php`, `src/Weaver.php`, `src/ClassList.php` — the cache: how a
  proxy's name is derived, when an existing file is reused, and how it is loaded. A proxy that is stale,
  misnamed, or read from a path chosen by someone else is as good as code execution.
- `src/FilePutContents.php` — the write: `tempnam()` in the target directory, `chmod(0666 & ~umask())`, `rename()`.
- `src/TokenIterator.php`, `src/TypeString.php`, `src/MethodSignatureString.php`, `src/ReflectionClass.php` —
  the source parsing and reflection the generator depends on.
- `src/Matcher/`, `src/Pointcut.php`, `src/Bind.php` — matching is application configuration; interesting when a
  wrong decision skips or adds interception.

`src-deprecated/` (the old `Cache` and `ServiceLocator`) is legacy, kept for 1.x compatibility, and out of scope.

## How to exercise it

Everything is installed; the network is already gone.

- `rayaop-test` runs PHPUnit over `tests/` (`vendor/bin/phpunit`). The suite already weaves real classes and
  inspects the generated source, so start by reading `tests/AopCodeTest.php`, `tests/CompilerTest.php` and
  `tests/WeavedFileReemissionTest.php`.
- `rayaop-demo` runs `php demo/run.php`, the three examples from the README.
- `tests/script/compile.php` and `tests/script/weave.php` are the small drivers the suite `exec()`s. Each one
  weaves a fixed fixture (`Ray\Aop\FakeMock` and `Ray\Aop\FakeWeaverScript`) into `tests/tmp`, so copy one and
  change the class to drive a case of your own.
- The quality tools are linked into `vendor/bin` from the bin namespace: `vendor/bin/psalm`, `vendor/bin/phpstan`,
  `vendor/bin/phpcs`, `vendor/bin/phpmd`, as `composer sa`, `cs` and `phpmd` call them.
- Generated proxies are ordinary PHP and `tests/tmp/` holds the ones the suite produced. Read them, and use
  `ReflectionClass::getFileName()` on a compiled class to find the file for a case you build yourself.

## How you rate severity

- Code execution, or a proxy loaded from a path the attacker chose, is critical.
- A proxy reused for a class it does not match — a name collision, or a key that stayed valid after the source
  changed — is high when it can drop, reorder or redirect interception, medium otherwise.
- A wrong matcher or interception decision on its own is medium; it is critical only with a path shown from
  untrusted input to it.
- Denial of service (a write that fills the directory, parsing that hangs) is medium.
- A finding that needs the cache directory to already be writable by another user, or a `unserialize()` of a file
  only the application writes, is low. Report it, but state the assumption it rests on.

## Anything to leave alone

- The `chmod(0666 & ~umask())` in `FilePutContents` is deliberate and matches the umask. It is only interesting
  with a scenario in which a different user shares that directory.
- `crc32` collisions in `AopPostfixClassName` are a known trade-off rather than a finding by itself. Report one
  only by naming two realistic inputs that collide and what goes wrong then.
- Coding standards, test coverage and the deprecated `src-deprecated/` API surface are out of scope.
