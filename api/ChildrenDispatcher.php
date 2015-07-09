<?php

trait ChildrenDispatcher
{

    public static function IsPrimaryKey($v)
    {
        return self::GetOne('id', $v);
    }

    private static $commonSubresource = array(
        'fields' => 'fieldsProc',
        'sessions' => 'sessionsProc',
        'bounds' => 'boundsProc',
        'notifications' => 'notificationsProc',
        'identify' => 'identifyProc',
        'objectBounds' => 'objectBoundsProc',
        'prev' => 'prevProc',
        'next' => 'nextProc');

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
                } else {
                    $request['response']['code'] = 400; //bad request
                }
                //$result['code'] = 406; //not acceptable
                break;
            case 'DELETE': // == delete media
                $count = count($request['paths']);
                if ($count == 1) {
                } else {
                    $request['response']['code'] = 400; //bad request
                }
                break;
            default:
                break;
        }
        return $request;
    }

    public function loginProcess()
    {
        $sessionId = rand();
        while (self::GetBySessionId($sessionId)) {
            $sessionId = rand();
        }
        $this->setSessionId((string)$sessionId);
        $now = time();
        $this->setLastOperationTime($now);
        $this->Update();
        return $sessionId;
    }

    public function logoutProcess($sessionId)
    {
        $isSelf = FALSE;
        if ($sessionId == 'me') {
            $isSelf = TRUE;
        }
        if (!$isSelf) {
            $self = self::GetBySessionId($sessionId);
            if ($self->id == $this->id) {
                $isSelf = TRUE;
            }
        }
        if ($isSelf) {
            $this->setSessionId(NULL);
            $this->Update();
        }
    }

    public function sessionsProc(array &$request)
    {
        //print 'process users sessions';
        switch ($request['method']) {
            case 'POST': // == login
                $count = count($request['paths']);
                if (($count == 0) && ($request['params']['filter'] == '')) {
                    $body = json_decode($request['body']);
                    if (isset($body->password)) {
                        //print 'has password send';
                        if ($body->password == $this->getPassword()) {
                            $sessionId = $this->loginProcess();
                            $request['response']['cookies']['sessionId'] = $sessionId;
                            $request['response']['cookies']['token'] = 'onlyForTest';
                            //print_r($this);
                            $request['response']['body'] = $this->toJson();
                            //print $this->toJson() . '<br />';
                            //print_r($request);
                        } else {
                            $request['response']['code'] = 404; //invalidate username or password
                            $request['response']['body'] = '{"state": "invalid username or password, try again"}';
                        }
                    } else {
                        $sessionId = $this->loginProcess();
                        $request['response']['cookies']['sessionId'] = $sessionId;
                        $request['response']['cookies']['token'] = 'onlyForTest';
                        //print_r($this);
                        $request['response']['body'] = $this->toJson();
                        //print $this->toJson() . '<br />';
                        //print_r($request);
                    }
                } else {
                    $request['response']['code'] = 400; //bad request
                }
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
                $request['response']['code'] = 404; //not login
                $count = count($request['paths']);
                if ($count == 1) {
                    //process the logout
                    $now = time();
                    $sessionId = array_shift($request['paths']);
                    $session = sessions::IsPrimaryKey($sessionId);
                    if (($now - $session->getLastOperationTime()) < 30 * 60) {
                        $session->setLastOperationTime($now);
                        $session->Update();
                        $request['response']['code'] = 200;
                    }
                } else {
                    $request['response']['code'] = 400; //bad request
                }
                //$result['code'] = 406; //not acceptable
                break;
            case 'DELETE': // == logout
                $count = count($request['paths']);
                if ($count == 1) {
                    //process the logout
                    $sessionId = array_shift($request['paths']);
                    $this->logoutProcess($sessionId);
                } else {
                    $request['response']['code'] = 400; //bad request
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

    private static $classCommonSubresource = array(
        'notifications' => 'commonNotificationsProc',
        'statistics' => 'commonStatisticsProc',
        'byMap' => 'commonByMapProc');

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

}

