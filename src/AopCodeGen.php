<?php

declare(strict_types=1);

namespace Ray\Aop;

use ArrayIterator;

use function array_keys;
use function array_slice;
use function class_exists;
use function explode;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function in_array;
use function is_array;
use function sys_get_temp_dir;
use function tempnam;
use function token_get_all;
use function unlink;

use const PHP_EOL;
use const T_CLASS;
use const T_EXTENDS;
use const T_FUNCTION;
use const T_STRING;

class AopCodeGen
{
    public function generate(\ReflectionClass $sourceClass, string $postfix, BindInterface $bind, array $traits = [InterceptTrait::class], string $replacement = 'return $this->_intercept(func_get_args(), __FUNCTION__);}')
    {
        $code = file_get_contents($sourceClass->getFileName());
        $tokens = token_get_all($code);
        $inClass = false;
        $inMethod = false;
        $curlyBraceCount = 0;
        $methodStarted = false;
        $className = '';
        $firstBraceAfterClassHit = false;
        $newCode = new AopCodeGenNewCode();
        $iterator = new ArrayIterator($tokens);
        $bindings = array_keys($bind->getBindings());

        for ($iterator->rewind(); $iterator->valid(); $iterator->next()) {
            $token = $iterator->current();
            [$id, $text] = is_array($token) ? $token : [null, $token];

            if ($id === T_CLASS) {
                $inClass = true;
                $newCode->add($text . ' ');
                continue;
            }

            if ($inClass && $id === T_STRING && empty($className)) {
                $className = $text;
                $newClassName = $text . $postfix;
                $newCode->add($newClassName . ' extends ' . $className . ' ');
                continue;
            }

            if ($text === '{' && $inClass && ! $firstBraceAfterClassHit) {
                $newCode->commit();
            }

            if ($inClass && $id === T_EXTENDS) {
                $iterator->next();  // Skip extends keyword
                $iterator->next();  // Skip class name
                $iterator->next();  // Skip space
                continue;
            }

            if ($text === ';' && $inClass && ! $inMethod) {
                $newCode->clear();
                continue;
            }

            if ($inClass && $text === '{' && ! $inMethod) {
                $newCode->add('{');
                if (! empty($traits)) {
                    $newCode->add(' use \\' . implode(', ', $traits) . '; ');
                    $newCode->commit();
                }

                continue;
            }

            if ($id === T_FUNCTION) {
                $inMethod = true;
                $methodStarted = false;
                $currentMethodName = $iterator->offsetGet($iterator->key() + 2)[1];
                if (! in_array($currentMethodName, $bindings)) {
                    $newCode->clear();
                    $newCode->ignore(true);
                }
            }

            if ($inMethod) {
                if ($text === '{') {
                    $curlyBraceCount++;
                    $methodStarted = true;
                } elseif ($text === '}') {
                    $curlyBraceCount--;
                    $newCode->ignore(false);
                }

                if ($methodStarted) {
                    if ($curlyBraceCount === 1 && $text === '{') {
                        $newCode->add('{ ' . $replacement . ' ');
                        continue;
                    }

                    if ($curlyBraceCount === 0) {
                        $newCode->commit();
                        $inMethod = false;
                        $methodStarted = false;
                        continue;
                    }

                    continue;  // We skip adding other contents inside the method
                }
            }

            $newCode->add($text);
        }

        $newCode->commit();
        $this->addParentClass($newCode, $sourceClass, $bind);

        return $newCode->code;
    }

    public function addParentClass(AopCodeGenNewCode $newCode, \ReflectionClass $sourceClass, BindInterface $bind): void
    {
        $parent = $sourceClass->getParentClass();
        if (! $parent) {
            return;
        }

        $parentCode = $this->generate($parent, '__tmp', $bind);
        $tempFile = tempnam(sys_get_temp_dir(), 'tmp_') . '.php';
        file_put_contents($tempFile, $parentCode);
        require $tempFile;
        unlink($tempFile);
        $parentClass = $parent->getName() . '__tmp';
        class_exists($parentClass);
        $reflectionClass = new ReflectionClass($parentClass);
        $methods = $reflectionClass->getMethods();
        $methodCode = '';
        foreach ($methods as $method) {
            $methodCode .= $this->extractLines($parentCode, $method->getStartLine(), $method->getEndLine()) . PHP_EOL . PHP_EOL;
        }

        $newCode->insert($methodCode);
        $newCode->commit();
    }

    private function extractLines($string, $startLine, $endLine)
    {
        $lines = explode(PHP_EOL, $string);
        $selectedLines = array_slice($lines, $startLine - 1, $endLine - $startLine + 1);

        return implode(PHP_EOL, $selectedLines);
    }
}
