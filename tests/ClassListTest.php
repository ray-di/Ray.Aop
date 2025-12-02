<?php

declare(strict_types=1);

namespace Ray\Aop;

use PHPUnit\Framework\TestCase;

use function class_exists;
use function file_put_contents;
use function glob;
use function is_dir;
use function iterator_to_array;
use function mkdir;
use function rmdir;
use function sys_get_temp_dir;
use function uniqid;
use function unlink;

class ClassListTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/ray_aop_test_' . uniqid();
        mkdir($this->tempDir);
    }

    protected function tearDown(): void
    {
        // Clean up temp files
        $files = glob($this->tempDir . '/*');
        if ($files) {
            foreach ($files as $file) {
                unlink($file);
            }
        }

        if (! is_dir($this->tempDir)) {
            return;
        }

        rmdir($this->tempDir);
    }

    public function testGetIteratorReturnsAllExistingClasses(): void
    {
        $classList = new ClassList(__DIR__ . '/../src');
        $classes = iterator_to_array($classList->getIterator());

        foreach ($classes as $class) {
            $this->assertTrue(class_exists($class));
        }
    }

    public function testGetClassNameReturnsNullForNonClassFile(): void
    {
        $file = $this->tempDir . '/not_a_class.php';
        file_put_contents($file, '<?php echo "hello";');

        $result = ClassList::getClassName($file);
        $this->assertNull($result);
    }

    public function testGetClassNameReturnsNullForFileWithoutPhpTag(): void
    {
        $file = $this->tempDir . '/no_php_tag.php';
        file_put_contents($file, 'This is not PHP code');

        $result = ClassList::getClassName($file);
        $this->assertNull($result);
    }

    public function testGetClassNameWithNamespacedClass(): void
    {
        // Use an existing class file to test namespace extraction
        $result = ClassList::getClassName(__DIR__ . '/../src/Bind.php');
        $this->assertEquals(Bind::class, $result);
    }

    public function testGetClassNameWithGlobalClass(): void
    {
        // Test file with global namespace class
        $result = ClassList::getClassName(__DIR__ . '/Fake/FakeGlobalNamespaced.php');
        $this->assertNotNull($result);
    }

    public function testGetIteratorSkipsInvalidFiles(): void
    {
        // Create a valid PHP file with a class that exists
        $validFile = $this->tempDir . '/ValidClass.php';
        file_put_contents($validFile, '<?php class TempTestClass123 {}');

        // Create an invalid PHP file (no class)
        $invalidFile = $this->tempDir . '/invalid.php';
        file_put_contents($invalidFile, '<?php echo "not a class";');

        $classList = new ClassList($this->tempDir);
        $classes = iterator_to_array($classList->getIterator());

        // Should not include any classes from temp dir since TempTestClass123 doesn't exist in autoloader
        $this->assertEmpty($classes);
    }

    public function testGetClassNameHandlesFileWithComments(): void
    {
        // Test with existing file that has comments
        $result = ClassList::getClassName(__DIR__ . '/../src/AopCode.php');
        $this->assertEquals(AopCode::class, $result);
    }

    public function testGetClassNameHandlesInterfaceFile(): void
    {
        $result = ClassList::getClassName(__DIR__ . '/../src/BindInterface.php');
        // Interfaces are not returned by getClassName since it looks for 'class' keyword
        $this->assertNull($result);
    }

    public function testGetClassNameWithEmptyNamespace(): void
    {
        $result = ClassList::getClassName(__DIR__ . '/Fake/FakeGlobalEmptyNamespaced.php');
        // Should handle empty namespace case
        $this->assertNotNull($result);
    }
}
