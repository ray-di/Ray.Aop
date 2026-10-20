# Ray.Aop.
#
# /src   the Ray.Aop git checkout (the repository named in project.yaml)
#
# Ray.Aop turns a class's own source file into a proxy subclass, writes that proxy as PHP into a cache directory
# and `require`s it. The generator (AopCode) and the cache key (AopPostfixClassName) are the project's most
# interesting surface, so the image installs what it takes to exercise them offline: the Composer dev
# dependencies with PHPUnit, the quality tools the project's composer scripts call from a bin namespace, the
# test suite and the demos that weave real classes.
FROM php:8.5-cli-trixie

ENV DEBIAN_FRONTEND=noninteractive \
    COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_NO_INTERACTION=1 \
    COMPOSER_MEMORY_LIMIT=-1

RUN apt-get update && apt-get install -y --no-install-recommends \
      ca-certificates git unzip procps \
    && rm -rf /var/lib/apt/lists/*
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# A normal development CLI: a malformed proxy shows up as a parse or type warning, so let every diagnostic through.
RUN { echo 'memory_limit=-1'; \
      echo 'error_reporting=E_ALL'; \
      echo 'display_errors=On'; \
      echo 'log_errors=On'; \
      echo 'error_log=/dev/stderr'; \
    } > /usr/local/etc/php/conf.d/oss-scanner.ini

# scanner contract: the checkout lives inside the image, at /src
COPY . /src
WORKDIR /src

# The repository does not commit a composer.lock for the project itself, so this resolves the dependency tree
# rather than installing a pinned one. The analysis tools (Psalm, PHPStan, PHP_CodeSniffer, PHPMD) sit in a bin
# namespace with their own committed lock and their own command, because the project's composer scripts call them
# by path. All of it has to be fetched now: the audit is offline.
RUN composer install --no-progress \
    && composer bin tools install --no-progress

# Where the suite and the demos leave generated proxies and scratch files.
RUN mkdir -p tests/tmp tests/tmp_unerase demo/tmp

# The project's own entry points, so both can be run from anywhere:
#   rayaop-test   PHPUnit over tests/, as `composer test`
#   rayaop-demo   the three README examples, as `php demo/run.php`
RUN printf '#!/bin/sh\ncd /src && exec vendor/bin/phpunit "$@"\n' > /usr/local/bin/rayaop-test \
    && printf '#!/bin/sh\ncd /src && exec php demo/run.php\n' > /usr/local/bin/rayaop-demo \
    && chmod +x /usr/local/bin/rayaop-test /usr/local/bin/rayaop-demo

# Check the image works by running the suite and the demos, but let neither abort the build: a failing test
# still leaves a usable image for the audit, and a build error would only be reported back as one.
RUN (vendor/bin/phpunit --no-progress || echo 'WARNING: PHPUnit reported failures') \
    && (php demo/run.php > /tmp/demo.log 2>&1 \
          && echo 'demos ran' \
          || { echo 'WARNING: demo/run.php reported failures'; tail -20 /tmp/demo.log; })
