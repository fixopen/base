<?php

trait ChildrenDispatcher
{

    //id processor

    public static function IsPrimaryKey($v)
    {
        return self::GetOne('id', $v);
    }

    //object children processor

    private static $commonSubresource = array();

    public static function RegisterObjectChildProcessor($child, $processor)
    {
        self::$commonSubresource[$child] = $processor;
    }

    public function ObjectChildrenProcess(array &$request)
    {
        if (count($request['paths'])) {
            $child = array_shift($request['paths']);
            if (array_key_exists($child, self::$commonSubresource)) {
                $childProcessor = $this->GetObjectChildProcessor[$child];
                $this->$childProcessor($request);
            } else {
                self::Process($request, $this);
            }
        } else {
            self::Process($request, $this);
        }
    }

    public static function CommonObjectChildProcessorRegister()
    {
        self::RegisterObjectChildProcessor('fields', 'fieldsProc');
        self::RegisterObjectChildProcessor('sessions', 'sessionsProc');
        self::RegisterObjectChildProcessor('notifications', 'notificationsProc');
        self::RegisterObjectChildProcessor('identify', 'identifyProc');
        self::RegisterObjectChildProcessor('bounds', 'boundsProc');
        self::RegisterObjectChildProcessor('objectBounds', 'objectBoundsProc');
        self::RegisterObjectChildProcessor('prev', 'prevProc');
        self::RegisterObjectChildProcessor('next', 'nextProc');
    }

    public static function SpecObjectChildProcessorRegister()
    {
        //do nothing
    }

    public static function ObjectChildProcessorRegister()
    {
        self::CommonObjectChildProcessorRegister();
        self::SpecObjectChildProcessorRegister();
    }

    public function fieldsProc(array &$request)
    {
        switch ($request['method']) {
            case 'POST': // == insert media
                $count = count($request['paths']);
                if (($count == 1) && ($request['params']['filter'] == '')) {
                    $fieldName = array_shift($request['paths']);
                    $reflect = new ReflectionClass($this);
                    if ($reflect->hasProperty($fieldName)) {
                        switch ($request['temp']['type']) {
                            case 'normal':
                                //insert to medias table
                                //update field's value to media's name
                                break;
                            case 'binary':
                                //create file to store upload media's content
                                break;
                            default:
                                break;
                        }
                    } else {
                        $request['response']['code'] = 400; //bad request
                    }
                } else {
                    $request['response']['code'] = 400; //bad request
                }
                break;
            case 'PUT': // == replace media
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'PATCH': // == update media
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'GET': // == get media
                $request['response']['code'] = 404; //not login
                $count = count($request['paths']);
                if ($count == 1) {
                    $fieldName = array_shift($request['paths']);
                    $reflect = new ReflectionClass($this);
                    if ($reflect->hasProperty($fieldName)) {
                        switch ($request['temp']['type']) {
                            case 'normal':
                                //get info from medias table
                                break;
                            case 'binary':
                                //get file from store download media's content
                                break;
                            default:
                                break;
                        }
                    } else {
                        $request['response']['code'] = 400; //bad request
                    }
                } else {
                    $request['response']['code'] = 400; //bad request
                }
                //$result['code'] = 406; //not acceptable
                break;
            case 'DELETE': // == delete media
                $count = count($request['paths']);
                if ($count == 1) {
                    $fieldName = array_shift($request['paths']);
                    $reflect = new ReflectionClass($this);
                    if ($reflect->hasProperty($fieldName)) {
                        switch ($request['temp']['type']) {
                            case 'normal':
                                //deletet info from medias table
                                break;
                            case 'binary':
                                //delete file from store media's content
                                break;
                            default:
                                break;
                        }
                    } else {
                        $request['response']['code'] = 400; //bad request
                    }
                } else {
                    $request['response']['code'] = 400; //bad request
                }
                break;
            default:
                break;
        }
        return $request;
    }

    public function boundsProc(array &$request)
    {
        $count = count($request['paths']);
        switch ($request['method']) {
            case 'POST':
                if (($count == 0) && ($request['params']['filter'] == '')) {
                    //insert the data to bounds
                } else {
                    $request['response']['code'] = 400; //bad request
                }
                break;
            case 'PUT':
                if ($count == 1) {
                    //update the bounds
                } else {
                    //batch update the bounds
                }
                break;
            case 'PATCH':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'GET':
                if ($count == 1) {
                    //select one bounds
                } else {
                    //select more the bounds
                }
                break;
            case 'DELETE':
                if ($count == 1) {
                    //delete the bounds
                } else {
                    //batch delete the bounds
                }
                break;
            default:
                break;
        }
        return $request;
    }

    public function notificationsProc(array &$request)
    {
        $count = count($request['paths']);
        switch ($request['method']) {
            case 'POST':
                if (($count == 0) && ($request['params']['filter'] == '')) {
                    //insert the data to notifications
                } else {
                    $request['response']['code'] = 400; //bad request
                }
                break;
            case 'PUT':
                if ($count == 1) {
                    //update the notifications
                } else {
                    //batch update the notifications
                }
                break;
            case 'PATCH':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'GET':
                if ($count == 1) {
                    //select one notifications
                } else {
                    //select more the notifications
                }
                break;
            case 'DELETE':
                if ($count == 1) {
                    //delete the notifications
                } else {
                    //batch delete the notifications
                }
                break;
            default:
                break;
        }
        return $request;
    }

    public function identityProc(array &$request)
    {
        $count = count($request['paths']);
        switch ($request['method']) {
            case 'POST':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'PUT':
                if (($count == 0) && ($request['params']['filter'] == '')) {
                    //update the identity
                } else {
                    $request['response']['response']['code'] = 400; //bad request
                }
                break;
            case 'PATCH':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'GET':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'DELETE':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            default:
                break;
        }
        return $request;
    }

    public function objectBoundsProc(array &$request)
    {
        $count = count($request['paths']);
        switch ($request['method']) {
            case 'POST':
                if (($count == 0) && ($request['params']['filter'] == '')) {
                    //insert the data to objectBounds
                } else {
                    $request['response']['code'] = 400; //bad request
                }
                break;
            case 'PUT':
                if ($count == 1) {
                    //update the objectBounds
                } else {
                    //batch update the objectBounds
                }
                break;
            case 'PATCH':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'GET':
                if ($count == 1) {
                    //select one objectBounds
                } else {
                    //select more the objectBounds
                }
                break;
            case 'DELETE':
                if ($count == 1) {
                    //delete the objectBounds
                } else {
                    //batch delete the objectBounds
                }
                break;
            default:
                break;
        }
        return $request;
    }

    private static function parseSliceInfo(array &$paths, $defaultCount)
    {
        $result = array('key' => 0, 'direction' => '', 'count' => $defaultCount);
        $count = count($paths);
        if ($count) { //has slice info
            $first = array_shift($paths);
            if ($first == 'newest') {
                $result['direction'] = $first;
                if ($count >= 2) {
                    $result['count'] = array_shift($paths);
                }
            } else {
                $result['key'] = $first;
                if ($count == 2) {
                    $result['direction'] = array_shift($paths);
                } else if ($count >= 3) {
                    $result['direction'] = array_shift($paths);
                    $result['count'] = array_shift($paths);
                }
            }
        } else {
            $result['direction'] = 'none';
        }
        return $result;
    }

    public function prevProc(array &$request)
    {
        $count = count($request['paths']);
        switch ($request['method']) {
            case 'POST':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'PUT':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'PATCH':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'GET':
                if ($count == 1) {
                    $r = self::SelectSlice($request['params']['filter'], $this->GetId(), 'prev', $request['paths'][0]);
                    $request['response']['body'] = self::ToArrayJson($r);
                } else {
                    $request['response']['code'] = 400; //bad request
                }
                break;
            case 'DELETE':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            default:
                break;
        }
        return $request;
    }

    public function nextProc(array &$request)
    {
        $count = count($request['paths']);
        switch ($request['method']) {
            case 'POST':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'PUT':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'PATCH':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'GET':
                if ($count == 1) {
                    $r = self::SelectSlice($request['params']['filter'], $this->GetId(), 'next', $request['paths'][0]);
                    $request['response']['body'] = self::ToArrayJson($r);
                } else {
                    $request['response']['code'] = 400; //bad request
                }
                break;
            case 'DELETE':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            default:
                break;
        }
        return $request;
    }

    //class children processor

    private static $classCommonSubresource = array();

    public static function RegisterClassChildProcessor($child, $processor)
    {
        self::$classCommonSubresource[$child] = $processor;
    }

    public static function GetClassChildrenProcessor($classChild)
    {
        $result = FALSE;
        if (array_key_exists($classChild, self::$classCommonSubresource)) {
            $result = self::$classCommonSubresource[$classChild];
        }
        return $result;
    }

    public static function CommonClassChildProcessorRegister()
    {
        self::RegisterClassChildProcessor('notifications', 'commonNotificationsProc');
        self::RegisterClassChildProcessor('statistics', 'commonStatisticsProc');
        self::RegisterClassChildProcessor('byMap', 'commonByMapProc');
        self::RegisterClassChildProcessor('updateSince', 'updateSinceProc');
        self::RegisterClassChildProcessor('groups', 'groupsProc');
        self::RegisterClassChildProcessor('top', 'topProc');
    }

    public static function SpecClassChildProcessorRegister()
    {
        //do nothing
    }

    public static function ClassChildProcessorRegister()
    {
        self::CommonClassChildProcessorRegister();
        self::SpecClassChildProcessorRegister();
    }

    public static function commonNotificationProc(array &$request)
    {
        $count = count($request['paths']);
        switch ($request['method']) {
            case 'POST':
                if (($count == 0) && ($request['filter'] == '')) {
                    //insert the data to notifications
                } else {
                    $request['response']['code'] = 400; //bad request
                }
                break;
            case 'PUT':
                if ($count == 1) {
                    //update the notifications
                } else {
                    //batch update the notifications
                }
                break;
            case 'PATCH':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'GET':
                if ($count == 1) {
                    //select one notifications
                } else {
                    //select more the notifications
                }
                break;
            case 'DELETE':
                if ($count == 1) {
                    //delete the notifications
                } else {
                    //batch delete the notifications
                }
                break;
            default:
                break;
        }
        return $request;
    }

    private static function parseStatisticsInfo(array &$paths)
    {
        $result = array();
        if (!empty($paths)) {
            $first = \array_shift($paths);
            if (!empty($paths)) {
                $result['method'] = \array_shift($paths);
                $result['item'] = $first;
                $result['stats'] = $result['method'] . '(' . $first . ')';
            } else {
                if ($first == 'count') {
                    $result['stats'] = 'count(*)';
                    $result['method'] = $first;
                }
            }
        }
        return $result;
    }

    private static $statsMethod = array('avg', 'binary_checksum',
        'bit_and', 'bit_or', 'bit_xor',
        'checksum', 'checksum_agg',
        'count', 'group_concat', 'first', 'last', 'max', 'min',
        'std', 'stddev_pop', 'stddev_samp', 'stddev', 'stdev', 'stdevp',
        'sum', 'var', 'varp', 'var_pop', 'var_samp', 'variance');

    public static function Stats($filter, $calc)
    {
        $result = -1;
        $whereClause = ' WHERE ' . $filter;
        if (empty($filter)) {
            $whereClause = '';
        }
        $query = 'SELECT ' . $calc . ' FROM ' . self::Mark(self::$tableName) . $whereClause;
        $r = Database::GetInstance()->query($query);
        if ($r) {
            foreach ($r as $row) {
                $result = $row[0];
            }
        }
        return $result;
    }

    public static function commonStatisticsProc(array &$request)
    {
        switch ($request['method']) {
            case 'POST':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'PUT':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'PATCH':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'GET':
                $calc = self::parseStatisticsInfo($request['paths']);
                if ($calc['method'] != '') {
                    $v = self::Stats($request['filter'], $calc['stats']);
                    $body = '{"method":' . self::JsonQuote($v) . '}';
                    if ($calc['item'] != '') {
                        $body = '{"item":' . $body . '}';
                    }
                    $request['response']['body'] = $body;
                } else {
                    $request['response']['code'] = 400; //bad request
                }
                break;
            case 'DELETE':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            default:
                break;
        }
        return $request;
    }

    public static function commonByMapProc(array &$request)
    {
        switch ($request['method']) {
            case 'POST':
                $request['response']['code'] = 405; //Method Not Allowed
                break;
            case 'PUT':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'PATCH':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'GET':
                //userId/userRoleMap/roleId/{roleId}
                $request['response']['code'] = 404; //not found
                $count = count($request['paths']);
                if ($count == 4) {
                    //$role = roles::GetOne('name', 'Administrator');
                    //$roleId = $role->getId();
                    $data = self::GetByMap($request['paths'][0], $request['paths'][1], $request['paths'][2], $request['paths'][3]);
                    $request['response']['code'] = 200;
                    $request['response']['body'] = self::ToArrayJson($data);
                } else {
                    $request['response']['code'] = 400; //bad request
                }
                break;
            case 'DELETE': // == logout
                $request['response']['code'] = 405; //Method Not Allowed
                break;
            default:
                break;
        }
        return $request;
    }

    public static function updateSinceProc(array &$request)
    {
        $count = count($request['paths']);
        switch ($request['method']) {
            case 'POST':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'PUT':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'PATCH':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'GET':
                if ($count == 1) {
                    $time = urldecode(array_shift($request['paths']));
                    $where = ' WHERE ' . self::mark('lastUpdateTime') . ' > CAST ( \'' . $time . '\' AS TIMESTAMP WITHOUT TIME ZONE) ORDER BY '  . self::mark('lastUpdateTime') . ' ASC ';
                    $books = self::CustomSelect($where);
                    $syncInfo = array();
                    foreach ($books as $book) {
                        $syncInfo[] = $book->toSyncJson();
                    }
                    $request['response']['body'] = '[' . implode(', ', $syncInfo) . ']';
                } else {
                    $request['response']['code'] = 400; //bad request
                    $request['response']['body'] = '{"state": "must include [time] path segment"}';
                }
                break;
            case 'DELETE':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            default:
                break;
        }
        return $request;
    }

    public static function groupsProc(array &$request)
    {
        $count = count($request['paths']);
        switch ($request['method']) {
            case 'POST':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'PUT':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'PATCH':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'GET':
                if ($count == 1) {
                    $groupName = array_shift($request['paths']);
                    //SELECT $groupName from self::tableName GROUP BY $groupName
                    $groups = self::GroupSelect($groupName);
                    $request['response']['body'] = '[' . implode(', ', $groups) . ']';
                } else {
                    $request['response']['code'] = 400; //bad request
                    $request['response']['body'] = '{"state": "must include [time] path segment"}';
                }
                break;
            case 'DELETE':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            default:
                break;
        }
        return $request;
    }

    public static function topProc(array &$request)
    {
        $count = count($request['paths']);
        switch ($request['method']) {
            case 'POST':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
                break;
            case 'PUT':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
                break;
            case 'PATCH':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            case 'GET':
                if ($count == 1) {
                    $result = array();
                    $whereClause = '';
                    $filter = $request['params']['filter'];
                    if ($filter != '') {
                        $filterJson = json_decode($filter);
                        $where = array();
                        foreach ($filterJson as $key => $value) {
                            $condition = self::specFilter($key, $value);
                            if ($condition == '') {
                                if (is_null($value)) {
                                    $where[] = self::Mark($key) . ' IS NULL';
                                } else {
                                    $where[] = self::Mark($key) . ' = ' . self::DatabaseQuote($value, self::GetTypeByName($key));
                                }
                            } else {
                                $where[] = $condition;
                            }
                        }
                        //print_r($where);
                        $whereClause = ' AND ' . implode(' AND ', $where);
                    }
                    $dir = array_shift($request['paths']);
                    switch ($dir) {
                        case 'follow':
                            $query = 'SELECT "bookId", count(*) AS "followCount" FROM "business" WHERE "action" = ' . "'Follow'" . $whereClause . ' GROUP BY "bookId" ORDER BY "followCount" LIMIT 10';
                            //print $query . '<br />';
                            $r = Database::GetInstance()->query($query, PDO::FETCH_ASSOC);
                            if ($r) {
                                foreach ($r as $row) {
                                    $item = new stdClass();
                                    $item->bookId = $row['bookId'];
                                    $item->followCount = $row['followCount'];
                                    $item->viewCount = 0;
                                    $item->downloadCount = 0;
                                    $result[] = $item;
                                }
                            }
                            //print_r($result);
                            $bookIds = array();
                            foreach ($result as $stats) {
                                $bookIds[] = $stats->bookId;
                            }
                            //print_r($bookIds);
                            //print implode(', ', $bookIds) . '<br />';
                            $query = 'SELECT "bookId", count(*) AS "viewCount" FROM "business" WHERE "action" = ' . "'View' AND " . '"bookId" IN (' . implode(', ', $bookIds) . ') GROUP BY "bookId"';
                            //print $query . '<br />';
                            $r = Database::GetInstance()->query($query, PDO::FETCH_ASSOC);
                            if ($r) {
                                foreach ($r as $row) {
                                    $bookId = $row['bookId'];
                                    foreach ($result as $stats) {
                                        if ($stats->bookId == $bookId) {
                                            $stats->viewCount = $row['viewCount'];
                                            break;
                                        }
                                    }
                                }
                            }
                            $query = 'SELECT "bookId", count(*) AS "downloadCount" FROM "business" WHERE "action" = ' . "'Download' AND " . '"bookId" IN (' . implode(', ', $bookIds) . ') GROUP BY "bookId"';
                            //print $query . '<br />';
                            $r = Database::GetInstance()->query($query, PDO::FETCH_ASSOC);
                            if ($r) {
                                foreach ($r as $row) {
                                    $bookId = $row['bookId'];
                                    foreach ($result as $stats) {
                                        if ($stats->bookId == $bookId) {
                                            $stats->downloadCount = $row['downloadCount'];
                                            break;
                                        }
                                    }
                                }
                            }
                            //print_r($result);
                            $request['response']['body'] = self::ToArrayJson($result);
                            //print $request['body'];
                            break;
                        case 'view':
                            $query = 'SELECT "bookId", count(*) AS "viewCount" FROM "business" WHERE "action" = ' . "'View'" . $whereClause . ' GROUP BY "bookId" ORDER BY "viewCount" LIMIT 10';
                            //print $query . '<br />';
                            $r = Database::GetInstance()->query($query, PDO::FETCH_ASSOC);
                            if ($r) {
                                foreach ($r as $row) {
                                    $item = new stdClass();
                                    $item->bookId = $row['bookId'];
                                    $item->viewCount = $row['viewCount'];
                                    $item->downloadCount = 0;
                                    $item->followCount = 0;
                                    $result[] = $item;
                                }
                            }
                            $bookIds = array();
                            foreach ($result as $stats) {
                                $bookIds[] = $stats->bookId;
                            }
                            $query = 'SELECT "bookId", count(*) AS "followCount" FROM "business" WHERE "action" = ' . "'Follow' AND " . '"bookId" IN (' . implode(', ', $bookIds) . ') GROUP BY "bookId"';
                            //print $query . '<br />';
                            $r = Database::GetInstance()->query($query, PDO::FETCH_ASSOC);
                            if ($r) {
                                foreach ($r as $row) {
                                    $bookId = $row['bookId'];
                                    foreach ($result as $stats) {
                                        if ($stats->bookId == $bookId) {
                                            $stats->followCount = $row['followCount'];
                                            break;
                                        }
                                    }
                                }
                            }
                            $query = 'SELECT "bookId", count(*) AS "downloadCount" FROM "business" WHERE "action" = ' . "'Download' AND " . '"bookId" IN (' . implode(', ', $bookIds) . ') GROUP BY "bookId"';
                            //print $query . '<br />';
                            $r = Database::GetInstance()->query($query, PDO::FETCH_ASSOC);
                            if ($r) {
                                foreach ($r as $row) {
                                    $bookId = $row['bookId'];
                                    foreach ($result as $stats) {
                                        if ($stats->bookId == $bookId) {
                                            $stats->downloadCount = $row['downloadCount'];
                                            break;
                                        }
                                    }
                                }
                            }
                            $request['response']['body'] = self::ToArrayJson($result);
                            break;
                        case 'download':
                            $query = 'SELECT "bookId", COUNT(*) AS "downloadCount" FROM "business" WHERE "action" = ' . "'Download'" . $whereClause . ' GROUP BY "bookId" ORDER BY "downloadCount" LIMIT 10';
                            //print $query . '<br />';
                            $r = Database::GetInstance()->query($query, PDO::FETCH_ASSOC);
                            if ($r) {
                                foreach ($r as $row) {
                                    $item = new stdClass();
                                    $item->bookId = $row['bookId'];
                                    $item->downloadCount = $row['downloadCount'];
                                    $item->viewCount = 0;
                                    $item->followCount = 0;
                                    $result[] = $item;
                                }
                            }
                            $bookIds = array();
                            foreach ($result as $stats) {
                                $bookIds[] = $stats->bookId;
                            }
                            $query = 'SELECT "bookId", count(*) AS "viewCount" FROM "business" WHERE "action" = ' . "'View' AND " . '"bookId" IN (' . implode(', ', $bookIds) . ') GROUP BY "bookId"';
                            //print $query . '<br />';
                            $r = Database::GetInstance()->query($query, PDO::FETCH_ASSOC);
                            if ($r) {
                                foreach ($r as $row) {
                                    $bookId = $row['bookId'];
                                    foreach ($result as $stats) {
                                        if ($stats->bookId == $bookId) {
                                            $stats->viewCount = $row['viewCount'];
                                            break;
                                        }
                                    }
                                }
                            }
                            $query = 'SELECT "bookId", count(*) AS "followCount" FROM "business" WHERE "action" = ' . "'Follow' AND " . '"bookId" IN (' . implode(', ', $bookIds) . ') GROUP BY "bookId"';
                            //print $query . '<br />';
                            $r = Database::GetInstance()->query($query, PDO::FETCH_ASSOC);
                            if ($r) {
                                foreach ($r as $row) {
                                    $bookId = $row['bookId'];
                                    foreach ($result as $stats) {
                                        if ($stats->bookId == $bookId) {
                                            $stats->followCount = $row['followCount'];
                                            break;
                                        }
                                    }
                                }
                            }
                            $request['response']['body'] = self::ToArrayJson($result);
                            break;
                        default:
                            $request['code'] = 400; //bad request
                            break;
                    }
                } else {
                    $request['code'] = 400; //bad request
                }
                break;
            case 'DELETE':
                $request['response']['code'] = 405; //Method Not Allowed
                //$result['code'] = 406; //not acceptable
                break;
            default:
                break;
        }
        //print_r($request);
        return $request;
    }

}

