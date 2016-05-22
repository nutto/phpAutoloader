<?php

class BaseTest extends PHPUnit_Framework_TestCase
{
    protected static $loader;

    public static function setUpBeforeClass()
    {
        var_dump(__DIR__);
        require_once __DIR__ . '/../src/AutoLoaderClass.php';

        self::$loader = new AutoLoaderClass(__DIR__);

        self::$loader->mapNamespace('Hard\Lesson', __DIR__ . '/Study/Math');
        self::$loader->mapNamespace('Hard\Lesson', __DIR__ . '/Study/History');
        self::$loader->mapNamespace('MostHomework\Lesson', __DIR__ . '/Study/Physics');

        self::$loader->register();
    }

    public function testMapNamespace()
    {
        self::$loader->mapNamespace('Easy\Lesson', __DIR__ . '/Study/English');
    }

    /**
     * @dataProvider rootDirProvider
     */
    public function testRootDirFile($class, $expected_dir)
    {
        $this->assertSame(self::$loader->loader($class), $expected_dir);
    }

    public function rootDirProvider()
    {
        return array(
            array('Guide', __DIR__ . '/Guide.php'),
        );
    }

    /**
     * @dataProvider subDirProvider
     */
    public function testSubDirFile($class, $expected_dir)
    {
        $this->assertSame(self::$loader->loader($class), $expected_dir);
    }

    public function subDirProvider()
    {
        return array(
            array('Study\Guide', __DIR__ . '/Study/Guide.php'),
            array('Study\Math\Guide', __DIR__ . '/Study/Math/Guide.php'),
            array('Study\English\Guide', __DIR__ . '/Study/English/Guide.php'),
        );
    }

    /**
     * @dataProvider mappedProvider
     */
    public function testMappedFile($class, $expected_dir)
    {
        $this->assertSame(self::$loader->loader($class), $expected_dir);
    }

    public function mappedProvider()
    {
        return array(
            array('Hard\Lesson\Guide', __DIR__ . '/Study/Math/Guide.php'),
        );
    }

    public function testMultiMappedFile()
    {
        $this->assertSame(self::$loader->loader(
            'Hard\Lesson\Homework'),
            __DIR__ . '/Study/History/Homework.php'
        );
    }

//    public function testCaseSensitivity()
//    {
//        $this->assertFalse(self::$loader->loader('Hard\Lesson\guide'));
//    }

    public function testNotExistsFile()
    {
        $this->assertFalse(self::$loader->loader('\Study\Biology\Guide'));
    }

    public function testFinalEffect()
    {
        new MostHomework\Lesson\Guide();
    }
}