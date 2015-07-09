<?php

/**
 * Created by PhpStorm.
 * User: fixopen
 * Date: 1/4/15
 * Time: 15:44
 */
class Model
{

    /*
     * 所有的表中，关联二进制内容的（比如：头像、录音文件等），都记录它们（二进制文件）的uri。
     * 数字、字符串、布尔在PHP中就以各自的类型存储，数据库中亦如此，序列化为JSON的时候，对于数字，用""扩住。
     * 对于时戳和时间段，数据库用它自己的类型，PHP用DateTime和DateInterval（？？还是time和秒数？？），JSON用标准格式字符串。
     * 对于数组类型，数据库用自己的类型，PHP用array()，JSON就用数组表示法。
     * JSON表达各种类型的数据时，采用PostgreSQL的表达方式：
     * CAST ( expression AS type )
     * expression::type
     * typename ( expression )
     * type 'string'
     * 'string'::type
     * CAST ( 'string' AS type )
     * typename ( 'string' )
     */
    private static $tableName;
    private static $types = array();

    public static function GetTableType()
    {
        if (count(self::$types) == 0 && self::$tableName != 'Model') {
            $r = DatabaseConnection::GetInstance()->query('SELECT * FROM ' . self::Mark(self::$tableName) . ' LIMIT 1', PDO::FETCH_ASSOC);
            if ($r) {
                $columnCount = $r->columnCount();
                for ($i = 0; $i < $columnCount; ++$i) {
                    $metaInfo = $r->getColumnMeta($i);
                    //var_dump($metaInfo); len precision pdo_type pgsql:oid
                    self::$types[$metaInfo['name']] = $metaInfo['native_type'];
                }
            }
        }
    }

    use PermissionChecker, ChildrenDispatcher, DatabaseAccessor, Processor;

    public static function MetaPrepare($tableName)
    {
        self::$tableName = $tableName;
        self::GetTableType();
        self::ObjectChildProcessorRegister();
        self::ClassChildProcessorRegister();
    }

    public function __construct()
    {
        foreach (self::$types as $name => $type) {
            $this->$name = NULL;
        }
    }

    public function __get($key)
    {
        return $this->$key;
    }

    public function __set($key, $value)
    {
        $this->$key = $value;
    }

}
