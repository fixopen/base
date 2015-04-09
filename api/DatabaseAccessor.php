<?php

trait DatabaseAccessor
{

    public static function ConvertJsonToWhere($filter)
    {
        $where = array();
        $filterJson = json_decode($filter);
        //print_r($filterJson);
        foreach ($filterJson as $key => $value) {
            if (is_null($value)) {
                $where[] = self::Mark($key) . ' IS NULL';
            } else {
                $where[] = self::Mark($key) . ' = ' . self::DatabaseQuote($value, self::GetTypeByName($key));
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

    public static function IsPrimaryKey($v)
    {
        return self::GetOne('id', $v);
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
        if ($this->id == 0) {
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

    public function Update()
    {
        $command = 'UPDATE ' . self::Mark(self::$tableName) . ' SET ' . implode(', ', $this->GetSetItems()) . ' WHERE ' . self::ConstructNameValueFilter('id', $this->id);
        //print $command . '<br />';
        $userId = 0;
        logs::log($userId, dataTypes::GetIdByName(self::$tableName), $this->id, 'UPDATE', 'update one data');
        return DatabaseConnection::GetInstance()->exec($command);
    }

    public static function BatchUpdate($value, $filter)
    {
        $whereClause = '';
        if (!empty($filter)) {
            $whereClause = ' WHERE ' . $filter;
        }
        $command = 'UPDATE ' . self::Mark(self::$tableName) . ' SET ' . implode(', ', $value->GetSetItems()) . $whereClause;
        $userId = 0;
        logs::log($userId, dataTypes::GetIdByName(self::$tableName), NULL, 'UPDATE', 'update data with ' . $filter);
        return Database::GetInstance()->exec($command);
    }

}

