<?php

function modifyClassNameAndReplaceMethods($code, $postfix, $traits = [], $replacement = "/* replacement */") {
    $tokens = token_get_all($code);
    $newCode = '';
    $inClass = false;
    $inMethod = false;
    $curlyBraceCount = 0;
    $methodStarted = false;
    $className = '';

    foreach ($tokens as $token) {
        list($id, $text) = is_array($token) ? $token : array(null, $token);

        if ($id == T_CLASS) {
            $inClass = true;
            $newCode .= $text . ' ';
            continue;
        }

        if ($inClass && $id == T_STRING && empty($className)) {
            $className = $text;
            $newClassName = $className . $postfix;
            $newCode .= $newClassName . ' extends ' . $className . ' ';
            continue;
        }

        if ($inClass && $text == '{' && !$inMethod) {
            $newCode .= '{';
            if (!empty($traits)) {
                $newCode .= ' use ' . implode(', ', $traits) . '; ';
            }
            continue;
        }

        if ($id == T_FUNCTION) {
            $inMethod = true;
            $methodStarted = false;
        }

        if ($inMethod) {
            if ($text === '{') {
                $curlyBraceCount++;
                $methodStarted = true;
            } elseif ($text === '}') {
                $curlyBraceCount--;
            }

            if ($methodStarted) {
                if ($curlyBraceCount === 1 && $text === '{') {
                    $newCode .= '{ ' . $replacement . ' ';
                    continue;
                } elseif ($curlyBraceCount === 0) {
                    $newCode .= '}';
                    $inMethod = false;
                    $methodStarted = false;
                    continue;
                } else {
                    continue;  // We skip adding other contents inside the method
                }
            }
        }

        $newCode .= $text;
    }

    return $newCode;
}

$code = "<?php
namespace My\Namespace;

/**
 * This is a test class.
 */
#[SomeAttribute]
class Test implements Interface1, Interface2 {
    public function method1(int \$arg1, Foo \$arg2) {
        echo 'Hello World';
    }

    /**
     * Another method
     */
    private function method2() {
        echo 'Goodbye';
    }
}
";

$modifiedCode = modifyClassNameAndReplaceMethods($code, 'Modified', ['SomeTrait']);

// Test, Foo, SomeTrait, Interface1, Interface2の空定義を追加
$additionalClasses = "\nclass Test {}\nclass Foo {}\ntrait SomeTrait {}\ninterface Interface1 {}\ninterface Interface2 {}\n";

$modifiedCode .= $additionalClasses;

echo "Generated code:\n";
echo $modifiedCode;

// 一時ファイルにコードを書き込む
$tempFile = tempnam(sys_get_temp_dir(), 'tmp_') . '.php';
file_put_contents($tempFile, $modifiedCode);

// 一時ファイルをrequireしてエラーがないか検証
require $tempFile;

// 使用後、一時ファイルを削除
unlink($tempFile);
