<?php

trait DatabaseAccessor
{

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
                case 'int2': //smallint smallserial int2vector
                case 'int4': //integer serial int4range
                case 'int8': //bigint bigserial int8range
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
                case 'timestamp': //timestamptz time timetz tsrange tstzrange abstime reltime interval tinterval date daterange
                    $result = "TIMESTAMP '" . $v . "'";
                    break;
                default:
                    //bit varbit bytea json xml uuid
                    //box circle line lseg path point polygon
                    //tsquery tsvector
                    //cid pg_node_tree oid oidvector gtsvector refcursor regclass regconfig regdictionary regoper regoperation regproc regprocedure regtype smgr tid xid txid_snapshot
                    break;
            }
        }
        return $result;
    }

    private function GetNameValuePairs() {
        $result = array();
        self::GetTableType(self::$tableName);
        foreach (self::$types as $key => $typeName) {
            $result[self::Mark($key)] = self::DatabaseQuote($this->$key, $typeName);
        }
        return $result;
    }

    public function GetSetItems($includeNull) {
        $result = array();
        $nameValues = $this->GetNameValuePairs();
        foreach ($nameValues as $name => $value) {
            if ($includeNull || $value) {
                $result[] = $name . ' = ' . $value;
            }
        }
        return $result;
    }

    public function FillSelfByRowArray($row)
    {
        foreach (self::$types as $key => $typeName) {
            $value = $row[$key];
            if ($value != NULL) {
                switch ($typeName) {
                    case 'int2': //smallint smallserial int2vector
                    case 'int4': //integer serial int4range
                    case 'int8': //bigint bigserial int8range
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
                $item->FillSelfByRowArray($row);
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
                $item->FillSelfByRowArray($row);
                $result[] = $item;
            }
        }
        //print 'got data<br />';
        //$userId = 0;
        //logs::log($userId, dataTypes::GetIdByName($className), $result->id, 'SELECT', 'read data');
        //print 'log it<br />';
        return $result;
    }

    private static function specWhereItemProcessor($name, $value)
    {
        return '';
    }

    public static function ConvertJsonToWhere($filter)
    {
        $where = array();
        $filterJson = json_decode($filter);
        //print_r($filterJson);
        foreach ($filterJson as $key => $value) {
            $whereItem = self::specWhereItemProcessor($key, $value);
            if ($whereItem == '') {
                if (is_null($value)) {
                    $where[] = self::Mark($key) . ' IS NULL';
                } else {
                    $where[] = self::Mark($key) . ' = ' . self::DatabaseQuote($value, self::GetTypeByName($key));
                }
            } else {
                $where[] = $whereItem;
            }
        }
        //print_r($where);
        return implode(' AND ', $where);
    }

    public static function ConstructNameValueFilter($name, $value)
    {
        return self::Mark($name) . ' = ' . self::DatabaseQuote($value, self::GetTypeByName($name));
    }

    public static function ConstructMapFilter($foreignName, $mapTable, $pairName, $pairValue)
    {
        return 'id IN ( SELECT ' . $foreignName . ' FROM ' . $mapTable . ' WHERE ' . $pairName . ' = ' . $pairValue . ' )';
    }

    private static function ConvertJsonToOrderBy($orderBy)
    {
        $orders = array();
        $orderByJson = json_decode($orderBy);
        foreach ($orderByJson as $key => $value) {
            $orders[] = self::Mark($key) . ' ' . strtoupper($value);
        }
        return implode(', ', $orders);
    }

    public static function GetOne($name, $value)
    {
        $whereClause = ' WHERE ' . self::ConstructNameValueFilter($name, $value);
        $query = 'SELECT ' . implode(', ', self::GetMarkedColumnNames()) . ' FROM ' . self::Mark(self::$tableName) . $whereClause . ' LIMIT 1';
        return self::GetOneData($query, self::$tableName);
    }

    public static function Select($params, $regionExpression)
    {
        //print 'Select<br />';
        $whereClause = '';
        $filter = $params['filter'];
        //print $filter . '<br />';
        if (!empty($filter)) {
            print 'not empty??';
            return;
            $whereClause = ' WHERE ' . self::ConvertJsonToWhere($filter) . ($regionExpression ? ' AND ( ' . $regionExpression . ' )' : '');
        }
        //print $whereClause . '<br />';

        $orderByClause = '';
        $orderBy = $params['orderBy'];
        if (!empty($orderBy)) {
            $orderByClause = ' ORDER BY ' . self::ConvertJsonToOrderBy($orderBy);
        }

        $pagedClause = '';
        $count = $params['count'];
        $offset = $params['offset'];
        if ($count != -1 && $offset != -1) {
            $pagedClause = ' LIMIT ' . $count . ' OFFSET ' . $offset;
        }

        $query = 'SELECT ' . implode(', ', self::GetMarkedColumnNames()) . ' FROM ' . self::Mark(self::$tableName) . $whereClause . $orderByClause . $pagedClause;
        //print $query . '<br />';

        return self::GetData($query, self::$tableName);
    }

    public static function GetByMap($foreignName, $mapTable, $pairName, $pairValue)
    {
        $whereClause = ' WHERE ' . self::ConstructMapFilter($foreignName, $mapTable, $pairName, $pairValue);
        $query = 'SELECT ' . implode(', ', self::GetMarkedColumnNames()) . ' FROM ' . self::Mark(self::$tableName) . $whereClause;
        //print $query;
        return self::GetData($query, self::$tableName);
    }

    public static function CustomSelect($whereClause)
    {
        $query = 'SELECT ' . implode(', ', self::GetMarkedColumnNames()) . ' FROM ' . self::Mark(self::$tableName) . $whereClause;
        //print $query;
        return self::GetData($query, self::$tableName);
    }

    public function Insert()
    {
        if (!isset($this->id) || $this->id == 0) {
            $this->id = IdGenerator::GetNewId();
        }
        $nameValues = $this->GetNameValues();
        $command = 'INSERT INTO ' . self::Mark(self::$tableName) . ' ( ' . implode(', ', array_keys($nameValues)) . ' ) VALUES ( ' . implode(', ', array_values($nameValues)) . ' )';
        DatabaseConnection::GetInstance()->exec($command);
        //if (isset($seqName)) {
        //    $this->id = Database::GetInstance()->lastInsertId($seqName);
        //} else {
        //    $this->id = Database::GetInstance()->lastInsertId();
        //}
        $userId = 0;
        logs::log($userId, dataTypes::GetIdByName(self::$tableName), $this->id, 'INSERT', 'create one data');
        return $this->id;
    }

    public function Delete()
    {
        $command = 'DELETE FROM ' . self::Mark(self::$tableName) . ' WHERE ' . self::ConstructNameValueFilter('id', $this->id);
        $userId = 0;
        logs::log($userId, dataTypes::GetIdByName(self::$tableName), $this->id, 'DELETE', 'delete one data');
        return DatabaseConnection::GetInstance()->exec($command);
    }

    public static function BatchDelete($filter)
    {
        $whereClause = '';
        if (!empty($filter)) {
            $whereClause = ' WHERE ' . $filter;
        }
        $command = 'DELETE FROM ' . self::Mark(self::$tableName) . $whereClause;
        $userId = 0;
        logs::log($userId, dataTypes::GetIdByName(self::$tableName), NULL, 'DELETE', 'delete data with ' . $filter);
        return DatabaseConnection::GetInstance()->exec($command);
    }

    public function Update($isUpdate)
    {
        $includeNull = TRUE;
        if ($isUpdate) {
            $includeNull = FALSE;
        }
        $command = 'UPDATE ' . self::Mark(self::$tableName) . ' SET ' . implode(', ', $this->GetSetItems($includeNull)) . ' WHERE ' . self::ConstructNameValueFilter('id', $this->id);
        //print $command . '<br />';
        $userId = 0;
        logs::log($userId, dataTypes::GetIdByName(self::$tableName), $this->id, 'UPDATE', 'update one data');
        return DatabaseConnection::GetInstance()->exec($command);
    }

    public static function BatchUpdate($value, $filter, $isUpdate)
    {
        $whereClause = '';
        if (!empty($filter)) {
            $whereClause = ' WHERE ' . $filter;
        }
        $includeNull = TRUE;
        if ($isUpdate) {
            $includeNull = FALSE;
        }
        $command = 'UPDATE ' . self::Mark(self::$tableName) . ' SET ' . implode(', ', $value->GetSetItems($includeNull)) . $whereClause;
        $userId = 0;
        logs::log($userId, dataTypes::GetIdByName(self::$tableName), NULL, 'UPDATE', 'update data with ' . $filter);
        return Database::GetInstance()->exec($command);
    }

}

