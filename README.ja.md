# Ray.Aop

## アスペクト指向フレームワーク

[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/badges/quality-score.png?b=2.x)](https://scrutinizer-ci.com/g/Ray-Di/Ray.Aop/?branch=2.x)
[![Code Coverage](https://scrutinizer-ci.com/g/ray-di/Ray.Aop/badges/coverage.png?b=2.x)](https://scrutinizer-ci.com/g/Ray-Di/Ray.Aop/?branch=2.x)
[![Build Status](https://travis-ci.org/ray-di/Ray.Aop.svg?branch=2.x)](https://travis-ci.org/ray-di/Ray.Aop)

[\[English\]](https://github.com/ray-di/Ray.Aop/blob/2.x/README.md)

**Ray.Aop** パッケージはメソッドインターセプションの機能を提供します。マッチするメソッドが実行される度に実行されるコードを記述する事ができます。トランザクション、セキュリティやログといった横断的な”アスペクト”に向いています。なぜならインターセプターが問題をオブジェクトというよりアスペクトに分けるからです。これらの用法はアスペクト指向プログラミング(AOP)と呼ばれます。

[Matcher](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MatcherInterface.php) は値を受け取ったり拒否したりするシンプルなインターフェイスです。例えばRay.Aopでは２つの **Matcher** が必要です。１つはどのクラスに適用するかを決め、もう１つはそのクラスのどのメソッドに適用するかを決めます。これらを簡単に利用するためのファクトリークラスがあります。

[MethodInterceptors](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInterceptor.php) はマッチしたメソッドが呼ばれる度に実行されます。呼び出しやメソッド、それらの引き数、インスタンスを調べる事ができます。横断的なロジックと委譲されたメソッドが実行されます。最後に返り値を調べて返します。インターセプターは沢山のメソッドに適用され沢山のコールを受け取るので、実装は効果的で透過的なものになります。

## 例：平日のメソッドコールを禁止する

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

そして、インターセプトするメソッドに適用します。

```php
<?php
class RealBillingService
{
    /**
     * @NotOnWeekends
     */
    public function chargeOrder(PizzaOrder $order, CreditCard $creditCard)
    {
```

次に、org.aopalliance.intercept.MethodInterceptorインターフェイスを実装したインターセプターを定義します。元のメソッドを実行するためには `$invocation->proceed()` と実行します。

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

設定完了しました。このコードでは「どのクラスでも」「メソッドに`@NotOnWeekends`アノテーションがある」という条件にマッチします。

```php
<?php

use Ray\Aop\Sample\Annotation\NotOnWeekends;
use Ray\Aop\Sample\Annotation\RealBillingService;

$pointcut = new Pointcut(
    (new Matcher)->any(),
    (new Matcher)->annotatedWith(NotOnWeekends::class),
    [new WeekendBlocker]
);
$bind = (new Bind)->bind(RealBillingService::class, [$pointcut]);
$billing = (new Weaver($bind, $tmpDir))->newInstance(RealBillingService::class, [], $bind);

try {
    echo $billing->chargeOrder();
} catch (\RuntimeException $e) {
    echo $e->getMessage() . "\n";
    exit(1);
}
```

全てをまとめ（土曜日まで待って）、メソッドをコールするとインターセプターにより拒否されます。

```
chargeOrder not allowed on weekends!
```

## メソッド名を指定したマッチ

```php
<?php
    $bind = (new Bind)->bindInterceptors('chargeOrder', [new WeekendBlocker]);
    $compiler = new Weaver($bind, $tmpDir);
    $billing = $compiler->newInstance('RealBillingService', [], $bind);
    try {
        echo $billing->chargeOrder();
    } catch (\RuntimeException $e) {
        echo $e->getMessage() . "\n";
        exit(1);
    }
```

## 独自のマッチャー

独自のマッチャーを作成することもでます。
`contains` マッチャーを作成するためには、２つのメソッドを持つクラスを提供する必要があります。
１つはクラスのマッチを行う`matchesClass`メソッド、もう１つはメソッドのマッチを行う`matchesMethod`です。いずれもマッチしたかどうかをブールで返します。

```php
use Ray\Aop\AbstractMatcher;
use Ray\Aop\Matcher;

class IsContainsMatcher extends AbstractMatcher
{
    /**
     * {@inheritdoc}
     */
    public function matchesClass(\ReflectionClass $class, array $arguments) : bool
    {
        list($contains) = $arguments;

        return (strpos($class->name, $contains) !== false);
    }

    /**
     * {@inheritdoc}
     */
    public function matchesMethod(\ReflectionMethod $method, array $arguments) : bool
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
$bind = (new Bind)->bind(RealBillingService::class, [$pointcut]);
$billing = (new Weaver($bind, $tmpDir))->newInstance(RealBillingService::class, [$arg1, $arg2]);
```

## パフォーマンス

`Weaver`オブジェクトはキャッシュ可能です。コンパイル、束縛、アノテーション読み込みコストを削減します。

```php
$weaver = unserialize(file_get_contentes('./serializedWever'));
$billing = (new Weaver($bind, $tmpDir))->newInstance(RealBillingService::class, [$arg1, $arg2]);
```

## 優先順位

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

## 制限

この機能の背後ではメソッドのインターセプションを事前にコードを生成する事で可能にしています。Ray.Aopはダイナミックにサブクラスを生成してメソッドをオーバーライドするインターセプターを適用します。

クラスとメソッドは以下のものである必要があります。

 * クラスは *final* ではない
 * メソッドは *public*


## インターセプター

呼び出されたメソッドをそのまま実行するだけのインターセプターは以下のようになります。

```php
class MyInterceptor implements MethodInterceptor
{
    public function invoke(MethodInvocation $invocation)
    {
        // メソッド実行前
        //
        
        // メソッド実行
        $result = invocation->proceed();
        
        // メソッド実行後
        //
                
        return $result;
    }
}
```

インターセプターに渡されるメソッド実行(`MethodInvocation`)オブジェクトは以下のメソッドを持ちます。


 * [`$invocation->proceed()`](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Joinpoint.php#L41) - メソッド実行
 * [`$invocation->getMethod()`](https://github.com/ray-di/Ray.Aop/blob/2.x/src/MethodInvocation.php#L30) -  メソッドリフレクションの取得
 * [`$invocation->getThis()`](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Joinpoint.php#L50) - オブジェクトの取得
 * [`$invocation->getArguments()`](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Invocation.php#L25) - 引数の取得
 * [`$invocation->getNamedArguments()`](https://github.com/ray-di/Ray.Aop/blob/2.x/src/Invocation.php#L32) - 名前付き引数の取得


拡張されたリフレクションはアノテーション取得のメソッドを持ちます。
 
```php
/** @var $method \Ray\Aop\ReflectionMethod */
$method = $invocation->getMethod();
/** @var $class \Ray\Aop\ReflectionClass */
$class = $invocation->getMethod()->getDeclaringClass();
```

 
 * [`$method->getAnnotations()`]() - メソッドアノテーションの取得
 * [`$method->getAnnotation($name)`]() 
 * [`$class->->getAnnotations()`]() - クラスアノテーションの取得
 * [`$class->->getAnnotation($name)`]()

## AOPアライアンス

このメソッドインターセプターのAPIは[AOPアライアンス](http://aopalliance.sourceforge.net/doc/org/aopalliance/intercept/MethodInterceptor.html)の部分実装です。

## 要件

* PHP 5.6+
* hhvm

## インストール

Ray.Aopの推奨インストール方法は、[Composer](https://github.com/composer/composer)でのインストールです。

```bash
# Ray.Aop を依存パッケージとして追加する
$ composer require ray/aop ~2.0
```

## Ray.Aopのテスト

Ray.Aopをソースからインストールし、ユニットテストとデモを実行するには次のようにします。

```bash
git clone https://github.com/ray-di/Ray.Aop.git
cd Ray.Aop
composer install
vendor/bin/phpunit
php demo/run.php
```

DIとAOPを統合したDIフレームワーク[Ray.Di](https://github.com/ray-di/Ray.Di)もご覧ください。

* この文書の大部分は [Guice/AOP](https://github.com/google/guice/wiki/AOP) から借用しています。
