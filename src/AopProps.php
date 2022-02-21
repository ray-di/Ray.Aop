<?php

declare(strict_types=1);

namespace Ray\Aop;

use Doctrine\Common\Annotations\Reader;
use PhpParser\BuilderFactory;
use PhpParser\Node\Stmt\Property;
use Ray\ServiceLocator\ServiceLocator;
use ReflectionClass;

use function serialize;

class AopProps
{
    /** @var Reader */
    private $reader;

    /** @var BuilderFactory */
    private $factory;

    public function __construct(BuilderFactory $factory)
    {
        $this->reader = ServiceLocator::getReader();
        $this->factory = $factory;
    }

    /**
     * @param ReflectionClass<object> $class
     *
     * @return Property[]
     */
    public function __invoke(ReflectionClass $class, CodeVisitor $visitor): array
    {
        $pros = [];
        $pros[] = $this->factory
            ->property('bind')
            ->makePublic()
            ->getNode();

        $pros[] =
            $this->factory->property('bindings')
                ->makePublic()
                ->setDefault([])
                ->getNode();

        $pros[] = $this->factory
            ->property('methodAnnotations')
            ->setDefault($this->getMethodAnnotations($class, $visitor))
            ->makePublic()
            ->getNode();
        $pros[] = $this->factory
            ->property('classAnnotations')
            ->setDefault($this->getClassAnnotation($class, $visitor))
            ->makePublic()
            ->getNode();
        $pros[] = $this->factory
            ->property('isAspect')
            ->makePrivate()
            ->setDefault(true)
            ->getNode();

        return $pros;
    }

    /**
     * @param ReflectionClass<object> $class
     */
    private function getMethodAnnotations(ReflectionClass $class, CodeVisitor $visitor): string
    {
        $methodsAnnotation = [];
        $methods = $class->getMethods();
        foreach ($methods as $method) {
            $annotations = $this->reader->getMethodAnnotations($method);
            if ($annotations === []) {
                continue;
            }

            $methodsAnnotation[$method->name] = $annotations;
            $visitor->addUses($annotations);
        }

        return serialize($methodsAnnotation);
    }

    /**
     * @param ReflectionClass<T> $class
     *
     * @template T of object
     */
    private function getClassAnnotation(ReflectionClass $class, CodeVisitor $visitor): string
    {
        $classAnnotations = $this->reader->getClassAnnotations($class);
        $visitor->addUses($classAnnotations);

        return serialize($classAnnotations);
    }
}
