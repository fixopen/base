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

    public static function MetaPrepare($tableName)
    {
        self::$tableName = $tableName;
        self::GetTableType();
    }

    use PermissionChecker, ChildrenProcessor, DatabaseAccessor, Processor;

    private static function Mark($n)
    {
        //return '`' . $n . '`';
        return '"' . $n . '"';
    }

    public static function GetTypeByName($columnName) {
        $result = FALSE;
        self::GetTableType();
        if (array_key_exists($columnName, self::$types)) {
            $result = self::$types[$columnName];
        }
        return $result;
    }

    private static function GetMarkedColumnNames() {
        $result = array();
        self::GetTableType();
        //print_r(self::$types);
        foreach (self::$types as $key => $typeName) {
            $result[] = self::Mark($key);
        }
        return $result;
    }

    private static function DatabaseQuote($v, $type)
    {
        $result = '';
        if (is_null($v)) {
            $result = 'NULL';
        } else {
            switch ($type) {
                case 'varchar': //char with max length
                case 'bpchar': //blank padding char with length
                case 'text': //any char
                case 'char': //one char
                case 'name': //64 char
                    $result = "'" . $v . "'";
                    break;
                case 'int2': //smallint smallserial
                case 'int4': //integer serial
                case 'int8': //bigint bigserial
                case 'float4': //real
                case 'float8': //double precision
                    $result = $v;
                    break;
                case 'bool':
                    $result = $v ? 'TRUE' : 'FALSE';
                    break;
                case 'cidr':
                case 'inet':
                case 'macaddr':
                    $result = $v;
                    break;
                case 'timestamp':
                    $result = "TIMESTAMP '" . $v . "'";
                    break;
            }
        }
        return $result;
    }

    private function GetNameValues() {
        $result = array();
        self::GetTableType(self::$tableName);
        foreach (self::$types as $key => $typeName) {
            //if (array_key_exists($key, (array)$this)) {
            $result[self::Mark($key)] = self::DatabaseQuote($this->$key, $typeName);
            //}
        }
        return $result;
    }

    public function GetSetItems() {
        $result = array();
        $nameValues = $this->GetNameValues();
        foreach ($nameValues as $name => $value) {
            $result[] = $name . ' = ' . $value;
        }
        return $result;
    }

    public function FillSelfByRow($row)
    {
        foreach (self::$types as $key => $typeName) {
            $value = $row[$key];
            if ($value != NULL) {
                switch ($typeName) {
                    case 'int2':
                    case 'int4':
                    case 'int8':
                        $value = intval($value);
                        break;
                    case 'float4': //real
                    case 'float8': //double precision
                        $value = floatval($value);
                        break;
                }
            }
            $this->$key = $value;
        }
    }

    private static function GetOneData($query, $className) {
        $result = FALSE;
        $r = DatabaseConnection::GetInstance()->query($query, PDO::FETCH_ASSOC);
        if ($r) {
            foreach ($r as $row) {
                $item = new $className;
                $item->FillSelfByRow($row);
                $result = $item;
                break;
            }
        }
        $userId = 0;
        logs::log($userId, dataTypes::GetIdByName($className), $result->id, 'SELECT', 'read one data');
        return $result;
    }

    private static function GetData($query, $className) {
        //print $query . '<br />';
        //print $className . '<br />';
        $result = array();
        $r = DatabaseConnection::GetInstance()->query($query, PDO::FETCH_ASSOC);
        if ($r) {
            foreach ($r as $row) {
                $item = new $className;
                $item->FillSelfByRow($row);
                $result[] = $item;
            }
        }
        //print 'got data<br />';
        //$userId = 0;
        //logs::log($userId, dataTypes::GetIdByName($className), $result->id, 'SELECT', 'read data');
        //print 'log it<br />';
        return $result;
    }

    private static function JsonMark($n)
    {
        return '"' . $n . '"';
    }

    private static function JsonQuote($v, $type)
    {
        $result = '';
        if (is_null($v)) {
            $result = 'null';
        } else {
            if ($type) {
                switch ($type) {
                    case 'varchar': //char with max length
                    case 'bpchar': //blank padding char with length
                    case 'text': //any char
                    case 'char': //one char
                    case 'name': //64 char
                        $result = '"' . $v . '"';
                        break;
                    case 'int2': //smallint smallserial
                    case 'int4': //integer serial
                    case 'int8': //bigint bigserial
                    case 'float4': //real
                    case 'float8': //double precision
                    case 'numeric':
                    case 'money':
                        $result = $v;
                        break;
                    case 'bool':
                        $result = $v ? 'true' : 'false';
                        break;
                }
            }
        }
        return $result;
    }

    public function FillSelfByJson($json)
    {
        foreach ($json as $key => $value) {
            $value = $json[$key];
            if ($value != NULL) {
                $type = self::GetTypeByName($key);
                if ($type) {
                    switch ($type) {
                        case 'int2':
                        case 'int4':
                        case 'int8':
                            $value = intval($value);
                            break;
                        case 'float4': //real
                        case 'float8': //double precision
                            $value = floatval($value);
                            break;
                    }
                }
            }
            $this->$key = $value;
        }
    }

    public function ToJson()
    {
        $fields = array();
        foreach (self::$types as $key => $type) {
            $fields[] = self::JsonMark($key) . ': ' . self::JsonQuote($this->$key, $type);
        }
        return '{' . implode(', ', $fields) . '}';
    }

    public function ConvertToJson()
    {
        return json_encode($this);
    }

    public static function ToArrayJson(array $values)
    {
        $va = array();
        foreach ($values as $item) {
            $va[] = $item->ToJson();
        }
        return '[' . implode(', ', $va) . ']';
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

/**
-- Table: test

-- DROP TABLE test;

CREATE TABLE test
(
id bigint, int8
c1 "char", char
c2 "char"[], _char
c3 abstime, abstime
c4 abstime[], _abstime
c5 bigint[], _int8
c6 bigserial NOT NULL, int8
c7 bit(1), bit
c8 bit varying(16), varbit
c9 bit varying(16)[], _varbit
c10 bit(1)[], _bit
c11 boolean, bool
c12 boolean[], _bool
c13 box, box
c14 box[], _box
c15 bytea, bytea
c16 bytea[], _bytea
c17 character(16), bpchar
c18 character varying(16), varchar
c19 character varying(16)[], _varchar
c20 character(16)[], _bpchar
c21 cid, cid
c22 cid[], _cid
c23 cidr, cidr
c24 cidr[], _cidr
c25 circle, circle
c26 circle[], _circle
c28 date, date
c29 date[], _date
c30 daterange, daterange
c31 daterange[], _daterange
c32 double precision, float8
c33 double precision[], _float8
c34 gtsvector, gtsvector
c35 gtsvector[], _gtsvector
c36 inet, inet
c37 inet[], _inet
c38 int2vector, int2vector
c39 int2vector[], _int2vector
c40 int4range, int4range
c41 int4range[], _int4range
c42 int8range, int8range
c43 int8range[], _int8range
c44 integer, int4
c45 integer[], _int4
c46 interval, interval
c47 interval(6)[], _interval
c48 json, json
c49 json[], _json
c50 line, line
c51 line[], _line
c52 lseg, lseg
c53 lseg[], _lseg
c54 macaddr, macaddr
c55 macaddr[], _macaddr
c56 money, money
c57 macaddr[], _macaddr
c58 name, name
c59 name[], _name
c60 numeric(8,4), numeric
c61 numeric(8,4)[], _numeric
c62 numrange, numrange
c63 numrange[], _numrange
c64 oid,
c65 oid[],
c66 oidvector,
c67 oidvector[],
c68 path,
c69 path[],
c70 pg_node_tree,
c71 point,
c72 point[],
c73 polygon,
c74 polygon[],
c75 real, float4
c76 real[], _float4
c77 refcursor,
c78 refcursor[],
c79 regclass,
c80 regclass[],
c81 regconfig,
c82 regconfig[],
c83 regdictionary,
c84 regdictionary[],
c85 regoper,
c86 regoperator[],
c88 regoperator,
c87 regoper[],
c89 regproc,
c90 regproc[],
c91 regprocedure,
c92 regprocedure[],
c93 regtype,
c94 regtype[],
c95 reltime, reltime
c96 reltime[], _reltime
c97 serial NOT NULL, int4
c98 smallint, int2
c100 smallserial NOT NULL, int2
c101 smgr,
c102 text, text
c104 tid,
c105 tid[],
c106 time(6) with time zone, timetz
c110 timestamp(6) with time zone, timestamptz
c111 timestamp(6) with time zone[], _timestamptz
c112 timestamp(6) without time zone, timestamp
c113 timestamp(6) without time zone[], _timestamp
c114 tinterval,
c115 tinterval[],
c116 tsquery,
c117 tsquery[],
c118 tsrange,
c119 tsrange[],
c120 tstzrange,
c121 tstzrange[],
c122 tsvector,
c123 tsvector[],
c124 txid_snapshot,
c125 txid_snapshot[],
c126 uuid, uuid
c127 uuid[], _uuid
c129 xid, xid
c130 xid[], _xid
c131 xml, xml
c132 xml[], _xml
c133 calltype,
c134 calltype[],
c135 grouptype,
c136 grouptype[],
c137 operation
)
WITH (
OIDS=FALSE
);
ALTER TABLE test
OWNER TO postgres;
 */