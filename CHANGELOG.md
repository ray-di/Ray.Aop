# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- Rector configuration for PHP 8.2+ code quality improvements
- Deprecation stubs for PECL extension support (`AspectPecl`, `PeclDispatcher`) in `src-deprecated/` for backward compatibility

### Changed
- **BREAKING**: Removed PECL extension support (refs #242)
  - Migration guide: Use proxy-based AOP with `$aspect->bind() + newInstance()` instead
  - See https://github.com/ray-di/Ray.Aop/issues/242 for details
  - Deprecated stubs throw `LogicException` with migration instructions
- Replaced `@readonly` annotations with native `readonly` keyword for PHP 8.2 optimization
- Removed incorrect immutability annotations (`@psalm-immutable`, `@psalm-pure`) from classes with Reflection API dependencies
- Updated `composer.json` autoload to include `src-deprecated/` for backward compatibility

## [2.19.1] - 2026-01-24

### Fixed
- `getAnnotation()` now matches child classes by default using `ReflectionAttribute::IS_INSTANCEOF` (#254, #255)

## [2.19.0] - 2025-11-17

### Changed
- **BREAKING**: Removed PECL extension support (#248, refs #242)
  - Migration: Use proxy-based AOP with `$aspect->bind() + newInstance()` instead
  - Backward-compatible deprecation stubs provided in `src-deprecated/`
- Replace `@readonly` annotations with native `readonly` keyword (#249)

## [2.18.0] - 2025-02-23

### Added
- Support for AOP targeting PHP 8.2 readonly classes (#238)
- Template annotation into ReflectionClass (#239)

## [2.17.3] - 2025-02-14

### Fixed
- Rolled back to the state of v2.17.1 to address a breaking change introduced in v2.17.2

### Impact
- If you are using v2.17.2, please update to v2.17.3 as soon as possible

## [2.17.2] - 2025-02-14 (DEPRECATED - Breaking BC)

**This release is deprecated for breaking BC. Reverted in 2.17.3.**

### Changed
- ~~Add support for readonly class in PHP 8.2 (#236)~~

### Contributors
- @ngmy made their first contribution in #236

## [2.17.1] - 2025-02-08

### Fixed
- Remove extra `<?php` (#228)
- Fix Windows test (#234)
- Fix return type of getConstructor (#233)

### Added
- Add centralized type definitions in Types.php (#229)

### Changed
- Update license copyright year(s) (#230)

### Contributors
- @hanahiroAze made their first contribution in #228

## [2.17.0] - 2024-11-15

### Added
- PHP 8.4 compatibility (#220)
- PHPDoc type annotations for Psalm (#221)
- Method parameter attributes support (#227)
- Stability section to README files (#226)

### Changed
- Update Demo using Aspect class (#222)
- Extract PECL code from Aspect to PeclAspect (#223)
- Binding multiple interceptors with multiple bindings in PECL AOP (#225)
- Organizing comments and unnecessary files (#224)

## [2.16.2] - 2024-09-03

### Fixed
- Bug with annotated with matcher (#219)

### Changed
- Update Aspect.php (#218)

### Contributors
- @denise-kao made their first contribution in #218

## [2.16.1] - 2024-08-27

### Fixed
- Fix an issue where the same class would not compile in multiple contexts (#217)

### Added
- Add PHP class diagram HTML page (#216)

### Changed
- Update Technical Information (#215)

## [2.16.0] - 2024-07-07 "Tanabata"

### Added
- **New `Aspect` class** - Simplifies API and improves PECL extension integration
- PECL extension support with `weave()` method
- PHP class diagram (#213)

### Changed
- **BREAKING**: API simplified - use `Aspect` class instead of `Pointcut`, `Bind`, and `Weaver`
- `newInstance` method is now part of `Aspect` class, not `Weaver`

### Migration Guide

**Before (2.0.0 to 2.15.x):**
```php
$pointcut = new Pointcut(
    (new Matcher())->any(),
    (new Matcher())->annotatedWith(NotOnWeekends::class),
    [new WeekendBlocker()]
);
$bind = (new Bind)->bind(RealBillingService::class, [$pointcut]);
$billing = (new Weaver($bind, $tmpDir))->newInstance(RealBillingService::class, []);
```

**After (2.16.0+):**
```php
$aspect = new Aspect();
$aspect->bind(
    (new Matcher())->any(),
    (new Matcher())->annotatedWith(NotOnWeekends::class),
    [new WeekendBlocker()]
);
$billing = $aspect->newInstance(RealBillingService::class);
```

## [2.15.2] - 2024-05-20

### Added
- Support enum in attribute arguments (#212)

## [2.15.1] - 2024-05-13

### Fixed
- Fixed to not make `mixed` nullable (#211)

## [2.15.0] - 2024-04-21

### Added
- PHP 8.3 compatibility (#204)
- Create own codegen (#205)
- Validate tmp directory (#209)

### Changed
- Update license copyright year(s) (#207)

## [2.14.0] - 2023-09-11

### Changed
- Soothe Psalm (#200)
- Ignore anonymous class compilation (#202)

### Removed
- Doctrine annotation reader (#203)

## [2.13.1] - 2023-01-13

### Changed
- Update license copyright year(s) (#198)
- Bump php-parser to ^4.13.2 (#199)

## [2.13.0] - 2022-12-12

### Added
- PHP 8.2 compatibility (#196)
- Create attribute_reader.php for [performance boost](https://ray-di.github.io/manuals/1.0/ja/performance_boost.html) (#197)

## [2.12.4] - 2022-09-28

### Changed
- Improve compile performance (#192, #195)

## [2.12.3] - 2022-06-24

### Added
- PHP 8.2-alpha support (#190)

## [2.12.2] - 2022-03-09

### Added
- Support PHP 7.2 (#189)

## [2.12.1] - 2022-02-21

### Fixed
- Fix wrong compiled method of parent (#188)

## [2.12.0] - 2022-01-28

### Added
- Parent method intercept (#181)
- Add update license year action (#184)

### Changed
- Bump PHPStan (#186)

### Contributors
- @NaokiTsuchiya made their first contribution in #181
- @github-actions made their first contribution in #185

## [2.11.0] - 2021-10-31

### Added
- PHP 8.1 support (#177)
- Template annotation into ReflectionMethod::getAnnotation (#179)

### Contributors
- @sasezaki made their first contribution in #179

## [2.10.0] - 2021-01-13

### Added
- **PHP 8 attributes support**
- Add [Service Locator](https://github.com/ray-di/Ray.Aop/blob/2.10.0/sl-src/ServiceLocator.php) for annotation/attribute reader

### Changed
- CI: Migrate from Travis CI to GitHub Actions
- Add phpmd check

[Unreleased]: https://github.com/ray-di/Ray.Aop/compare/2.19.1...HEAD
[2.19.1]: https://github.com/ray-di/Ray.Aop/compare/2.19.0...2.19.1
[2.19.0]: https://github.com/ray-di/Ray.Aop/compare/2.18.0...2.19.0
[2.18.0]: https://github.com/ray-di/Ray.Aop/compare/2.17.2...2.18.0
[2.17.3]: https://github.com/ray-di/Ray.Aop/compare/2.17.2...2.17.3
[2.17.2]: https://github.com/ray-di/Ray.Aop/compare/2.17.1...2.17.2
[2.17.1]: https://github.com/ray-di/Ray.Aop/compare/2.17.0...2.17.1
[2.17.0]: https://github.com/ray-di/Ray.Aop/compare/2.16.2...2.17.0
[2.16.2]: https://github.com/ray-di/Ray.Aop/compare/2.16.1...2.16.2
[2.16.1]: https://github.com/ray-di/Ray.Aop/compare/2.16.0...2.16.1
[2.16.0]: https://github.com/ray-di/Ray.Aop/compare/2.15.2...2.16.0
[2.15.2]: https://github.com/ray-di/Ray.Aop/compare/2.15.1...2.15.2
[2.15.1]: https://github.com/ray-di/Ray.Aop/compare/2.15.0...2.15.1
[2.15.0]: https://github.com/ray-di/Ray.Aop/compare/2.14.0...2.15.0
[2.14.0]: https://github.com/ray-di/Ray.Aop/compare/2.13.1...2.14.0
[2.13.1]: https://github.com/ray-di/Ray.Aop/compare/2.13.0...2.13.1
[2.13.0]: https://github.com/ray-di/Ray.Aop/compare/2.12.4...2.13.0
[2.12.4]: https://github.com/ray-di/Ray.Aop/compare/2.12.3...2.12.4
[2.12.3]: https://github.com/ray-di/Ray.Aop/compare/2.12.2...2.12.3
[2.12.2]: https://github.com/ray-di/Ray.Aop/compare/2.12.1...2.12.2
[2.12.1]: https://github.com/ray-di/Ray.Aop/compare/2.12.0...2.12.1
[2.12.0]: https://github.com/ray-di/Ray.Aop/compare/2.11.0...2.12.0
[2.11.0]: https://github.com/ray-di/Ray.Aop/compare/2.10.0...2.11.0
[2.10.0]: https://github.com/ray-di/Ray.Aop/compare/2.9.9...2.10.0
