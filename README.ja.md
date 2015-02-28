# Ray.Aop

## Aspect Oriented Framework

[[English]](https://github.com/koriym/Ray.Aop/blob/develop/README.md)

**Ray.Aop** パッケージはメソッドインターセプションの機能を提供します。マッチするメソッドが実行される度に実行されるコードを記述する事ができます。トランザクション、セキュリティやログといった横断的な”アスペクト”に向いています。なぜならインターセプターが問題をオブジェクトというよりアスペクトに分けるからです。これらの用法はアスペクトオリエンティッドプログラム(AOP)と呼ばれます。

[Matcher](http://bearsunday.github.io/builds/Ray.Aop/api/class-Ray.Aop.Matchable.html) は値を受け取ったり拒否したりするシンプルなインターフェイスです。例えばRay.Aopでは２つの **Matcher** が必要です:１つはどのクラスに適用するかを決め、もう一つはそのクラスのどのメソッドに適用するかを決めます。これらを簡単に利用するためのファクトリークラスがあります。

[MethodInterceptors](http://bearsunday.github.io/builds/Ray.Aop/api/class-Ray.Aop.MethodInterceptor.html) はマッチしたメソッドが呼ばれる度に実行されます。呼び出しやメソッド、それらの引き数、インスタンスを調べる事ができます。横断的なロジックと委譲されたメソッドが実行されます。最後に返り値を調べて返します。インターセプターは沢山のメソッドに適用され沢山のコールを受け取るので、実装は効果的で透過的なものになります。


Example: Forbidding method calls on weekends
--------------------------------------------

メソッドインターセプターがRay.Aopでどのように機能するかを明らかにするために、週末にはピザの注文を禁止するようにしてみましょう。デリバリーは平日だけ受け付ける事にして、ピザの注文を週末には受け付けないようにします！この例はAOPで認証を使用するときにのパターンと構造的に似ています。

週末だけにするための[アノテーション](http://docs.doctrine-project.org/projects/doctrine-common/en/latest/reference/annotations.html)を定義します。

```php
<?php
/**
 * NotOnWeekends
 *
 * @Annotation
 * @Target("METHOD")
 */
final class NotOnWeekends
{
}
```

インターセプトさせるメソッドに適用します。

```php
<?php
class RealBillingService
{
    /**
     * @NotOnWeekends
     */
    chargeOrder(PizzaOrder $order, CreditCard $creditCard)
    {
```

次に、MethodInterceptorインターフェイスを実装します。元のメソッドを実行するためには **$invocation->proceed()** と実行します。

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

設定完了しました。このコードでは「どのクラスでも」「メソッドに@NotOnWeekendsアノテーション」という条件にマッチします。

```php
<?php
$bind = new Bind;
$matcher = new Matcher;
$interceptors = [new WeekendBlocker];
$pointcut = new Pointcut(
		$matcher->any(),
		$matcher->annotatedWith('Ray\Aop\Sample\Annotation\NotOnWeekends'),
		$interceptors
);
$bind->bind('Ray\Aop\Sample\AnnotationRealBillingService', [$pointcut]);

$compiler = new Compiler(__DIR__ . '/tmp');
$billing = $compiler->newInstance('RealBillingService', [], $bind);
try {
    echo $billing->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
```

全てをまとめ（土曜日まで待って）、メソッドをコールするとインターセプターにより拒否されます。

```php
<?php
RuntimeException: chargeOrder not allowed on weekends! in /apps/pizza/WeekendBlocker.php on line 14

Call Stack:
    0.0022     228296   1. {main}() /apps/pizza/main.php:0
    0.0054     317424   2. Ray\Aop\Weaver->chargeOrder() /apps/pizza/main.php:14
    0.0054     317608   3. Ray\Aop\Weaver->__call() /libs/Ray.Aop/src/Weaver.php:14
    0.0055     318384   4. Ray\Aop\ReflectiveMethodInvocation->proceed() /libs/Ray.Aop/src/Weaver.php:68
    0.0056     318784   5. Ray\Aop\Sample\WeekendBlocker->invoke() /libs/Ray.Aop/src/ReflectiveMethodInvocation.php:65
```

Explicit method name match
---------------------------

```php
	$bind = (new Bind)->bindInterceptors('chargeOrder', [new WeekendBlocker]);
    $compiler = new Compiler($tmpDir);
	$billing = $compiler->newInstance('RealBillingService', [], $bind);
	try {
	   echo $billing->chargeOrder();
	} catch (\RuntimeException $e) {
	   echo $e->getMessage() . "\n";
	   exit(1);
	}
```

My matcher
----------
独自のMatcherを作成するためには、クラスのマッチを行う`matchesClass`メソッドとメソッドのマッチを行う`matchesMethod`を実装して
`AbstractMatcher`クラスを継承したクラスを作成します。コンストラクタに条件を与えて利用します。


```php
use Ray\Aop\AbstractMatcher;
use Ray\Aop\Matcher;

class IsContainsMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments)
    {
        list($contains) = $arguments;

        return (strpos($class->name, $contains) !== false);
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments)
    {
        list($contains) = $arguments;

        return (strpos($method->name, $contains) !== false);
    }
}
```

```php
$pointcut = new Pointcut(
		(new Matcher)->any(),
		new IsContainsMatcher('charge'),
		[new WeekendBlocker]
);
$bind = new Bind(RealBillingService::class, [$pointcut]);
$billing = (new Compiler($tmpDir))->newInstance(RealBillingService::class, [], $bind);
```

Priority
--------
インターセプターの実行順は以下のルールで決定されます。

 * 基本的にはバインドした順に実行されます。
 * `PriorityPointcut`で定義したものが最も優先されます。
 * アノテーションでメソッドマッチするものは`PriorityPointcut`の次に優先されます。その時アノテートされた順で優先されます。

```php
/**
 * @Auth    // 1st
 * @Cache   // 2nd
 * @Log     // 3rd
 */
```


Limitations
-----------

この機能の背後ではメソッドのインターセプションを事前にコードを生成する事で可能にしています。Ray.Aopはダイナミックにサブクラスを生成してメソッドをオーバーライドするインターセプターを適用します。
クラスとメソッドは以下のものである必要があります。

 * クラスは *final* ではない
 * メソッドは *public*
 * メソッドは *final* ではない

AOP Alliance
------------
このメソッドインターセプターのAPIは[AOP Alliance](http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/MethodInterceptor.html)の部分実装です。

Requirements
------------

* PHP 5.4+
* hhvm

Installation
------------

The recommended way to install Ray.Aop is through [Composer](https://github.com/composer/composer).

```bash
# Add Ray.Aop as a dependency
$ composer require ray/aop ~2.0@dev
```

Testing Ray.Aop
===============

```
$ composer create-project ray/aop:~2.0 Ray.Aop
$ cd Ray.Aop
$ phpunit
$ php docs/demo/run.php
```

DIとAOPを統合したDIフレームワーク[Ray.Di](https://github.com/koriym/Ray.Di)もご覧ください。

* The most part of this documentation is taken from [Guice/AOP](https://github.com/google/guice/wiki/AOP)
