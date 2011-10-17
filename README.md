# Ray.Aop
## Aspect Oriented Programming for PHP###

 * _this is preview release_

To compliment dependency injection, Ray.Aop supports method interception. This feature enables you to write code that is executed each time a matching method is invoked. It's suited for cross cutting concerns ("aspects"), such as transactions, security and logging. Because interceptors divide a problem into aspects rather than objects, their use is called Aspect Oriented Programming (AOP).

## Getting Started

Original class

	class Mock
	{
	    public function getDouble($a)
	    {
	        return $a * 2;
	    }
	}

Intercepter

	class tenTimes implements MethodInterceptor
	{
	    public function invoke(MethodInvocation $invocation)
	    {
	        $result = $invocation->proceed();
	        return $result * 10;
	    }
	}

Weave original class and interceptor with Weaver.

	// with weaver
	$mock = new Weaver(new Mock, array(new tenTimes, new tenTimes));
	echo $mock->getDouble(3); //600 =3*2*10*10

	// in original
	$mock = new Mock;
	echo $mock->getDouble(3); //6

# Usage

 * Manual weave

## Manual Weave

	$weavedBilling = new Weaver(new RealBilling, array(new WeekendBlocker));
	$weavedBilling->chargeOrder($args);