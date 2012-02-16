Ray.Aop
=======

Ray.Aop package provides method interception. This feature enables you to write code that is executed each time a matching method is invoked. It's suited for cross cutting concerns ("aspects"), such as transactions, security and logging. Because interceptors divide a problem into aspects rather than objects, their use is called Aspect Oriented Programming (AOP).

[![Build Status](https://secure.travis-ci.org/koriym/Ray.Aop.png)](http://travis-ci.org/koriym/Ray.Aop)

Requiement
-------------

 * PHP 5.4
 
Getting Started
===============

Target class

```php
<?php
class RealBillingService
{
	/**
	 * @WeekendBlock
	 */
	public function chargeOrder()
	{
	    echo "Charged.\n";
	}
}
```

Intercepter, which block weekend charge.

```php
<?php
class WeekendBlocker implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        $today = getdate();
        if ($today['weekday'][0] === 'S') {
            throw new \RuntimeException(
          		$invocation->getMethod()->getName() . " not allowed on weekends!"
            );
        }
        return $invocation->proceed();
    }
}
```

Weave interceptor with explicit method name.

```php
<?php
	$bind = new Bind;
	$bind->bindInterceptors('chargeOrder', [new WeekendBlocker]);
	
	$billingService = new Weaver(new RealBillingService, $bind);
	try {
	   echo $billingService->chargeOrder();
	} catch (\RuntimeException $e) {
	   echo $e->getMessage() . "\n";
	   exit(1);
	}
```

Or use 'annotation matcher', Ray.Aop supports doctrine.common.annotation.

```php
<?php
$bind = new Bind;
$matcher = new Matcher(new Reader);
$interceptors = [new WeekendBlocker];
$pointcut = new Pointcut(
		$matcher->any(),
		$matcher->annotatedWith('Ray\Aop\Sample\Annotation\WeekendBlock'),
		$interceptors
);
$bind->bind('Ray\Aop\Sample\AnnotationRealBillingService', [$pointcut]);

$billing = new Weaver(new RealBillingService, $bind);
try {
    echo $billing->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
```

Testing Ray.Aop
=======

Here's how to install Ray.Aop from source to run the unit tests and sample:

```
$ git clone git://github.com/koriym/Ray.Aop.git
$ git submodule update --init
$ phpunit
$ php doc/sample-01-quick-weave/main.php
// Charged.
```