<?php

/**
 * Class AutoLoaderClass
 *
 * 这一个本人学习PHP自动加载类的测试
 *
 * 这个类希望实现PSR4中通过命名空间加载文件的3个规范
 * + 完整类名中不包括顶级命名空间的分隔符和子命名空间(被视作命名空间前缀)对应至少一个根目录
 * + 命名空间前缀对应的就是该根目录下的子目录,命名空间的分隔符就是目录的分隔符
 * + 最后的类名对应的就是目录下与类名大小写完全一致的.php文件
 *
 * 举例
 * +--------------------------+--------------+-----------------------+------------------------------------+
 * |       全限定的类名         |     前缀      |         根目录         |              映射结果               |
 * +--------------------------+--------------+-----------------------+------------------------------------+
 * |     \Hard\Lesson\Guide   | \Hard\Lesson |   __DIR__\Study\Math  |    __DIR__\Study\Math\Guide.php    |
 * +--------------------------+--------------+-----------------------+------------------------------------+
 * |   \Hard\Lesson\Homework  | \Hard\Lesson | __DIR__\Study\History | __DIR__\Study\History\Homework.php |
 * +--------------------------+--------------+-----------------------+------------------------------------+
 * |          \Guide          |       \      |        __DIR__        |          __DIR__\Guide.php         |
 * +--------------------------+--------------+-----------------------+------------------------------------+
 * | \Study\English\Guide.php |       \      |        __DIR__        |   __DIR__\Study\English\Guide.php  |
 * +--------------------------+--------------+-----------------------+------------------------------------+
 *
 * @author: Nutto
 */
class AutoLoaderClass
{
    /**
     * 命名空间与路径的映射表
     * @var array
     */
    protected $mapped_prefix = array();

    /**
     * 默认加载路径的根目录
     * @var string
     */
    protected static $root_dir;

    /**
     * AutoLoaderClass constructor.
     *
     * 允许修改包含操作的根目录
     *
     * @param string $root_dir
     */
    public function __construct($root_dir=__DIR__)
    {
        self::$root_dir = $root_dir;
    }

    /**
     * 增加命名空间和具体目录路径的映射
     *
     * @param string $prefix
     * @param string $dir
     * @param bool $prepend
     */
    public function mapNamespace($prefix, $dir, $prepend=false)
    {
        // 由于自动加载函数回调传参是完全限定的,所以为了后续处理的统一
        // 前后的斜杠和无关的字符都去除
        $prefix = trim($prefix, " \t\n\r\0\x0B\\/");
        $dir = rtrim(trim($dir), '\\/');

        // 初始化不存在的映射
        if (!isset($this->mapped_prefix[$prefix])) {
            $this->mapped_prefix[$prefix] = array();
        }

        // 插入时可以决定使用的优先级
        if ($prepend) {
            array_unshift($this->mapped_prefix[$prefix], $dir);
        } else {
            array_push($this->mapped_prefix[$prefix], $dir);
        }
    }

    public function register()
    {
        spl_autoload_register(array($this, 'loader'), true, false);
    }

    public function loader($class)
    {
        $prefix = $class;

        // 先查看是否存在有映射的路径的命名空间
        while (($pos = strrpos($prefix, '/')) !== false) {
            $relative_path = substr($class, $pos + 1);
            $prefix = substr($class, 0, $pos);

            $file = $this->loadMappedFile($prefix, $relative_path);
            if ($file != false) {
                return $file;
            }
        }

        // 尝试通过默认的路径加载文件
        $file = self::$root_dir . '/' . $class . '.php';
        if ($this->requireFile($file)) {
            return $file;
        }

        // 没有匹配的文件
        return false;
    }

    /**
     * 加载存在映射的路径的文件
     *
     * @param $prefix
     * @param $relative_path
     * @return bool|string
     */
    public function loadMappedFile($prefix, $relative_path) {
        if (isset($this->mapped_prefix[$prefix])) {
            foreach ($this->mapped_prefix[$prefix] as $root_dir) {
                $file = $root_dir . '/' . $relative_path . '.php';
                if ($this->requireFile($file)) {
                    return $file;
                }
            }
        }
        return false;
    }

    /**
     * 加载文件
     *
     * @param $path
     * @return bool
     */
    protected function requireFile($path)
    {
        if (file_exists($path)) {
            require_once $path;
            return true;
        }
        return false;
    }
}