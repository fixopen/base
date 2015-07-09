<?php

class sessions extends Model
{

    public static function IsPrimaryKey($v)
    {
        //print 'user key is ' . $v . '<br />';
        $result = self::GetOne('sessionId', $v);
        if ($result == FALSE) {
            $result = self::isPrimary($v);
        }
        return $result;
    }

    public static function GetByUserId($userId)
    {
        return self::GetOne('userId', $userId);
    }

    public static function Touch($sessionId)
    {
        $session = self::IsPrimaryKey($sessionId);
        if ($session) {
            $session->lastOperationTime = time();
            $session->Update();
        }
    }

    private static function BeforePost(array &$request)
    {
        $data = json_decode($request['body']);
        $user = users::validEvidence($data);
        if ($user) {
            $session = self::GetByUserId($user->id);
            if ($session) {
                $session->sessionId = NULL/*unique value*/;
                $session->lastUpdateTime = time();
                $session->Update(TRUE);
                $request['temp']['continue'] = FALSE;
            } else {
                $session = new sessions();
                $session->sessionId = NULL/*unique value*/;
                $session->lastUpdateTime = time();
                $session->Insert();
                $request['temp']['continue'] = TRUE;
            }
        } else {
            $request['response']['code'] = 404;
            $request['temp']['continue'] = FALSE;
        }
    }

    private static function BeforeDelete(array &$request)
    {
        $session = self::IsPrimaryKey(array_shift($request['paths']));
        if ($session) {
            $session->sessionId = NULL;
            $session->Update(FALSE);
        } else {
            $request['response']['code'] = 404;
        }
        $request['temp']['continue'] = FALSE;
    }

    private static function AfterGet(array &$request)
    {
        //
    }

    public static function GetBySessionId($sessionId)
    {
        return self::GetOne('sessionId', $sessionId);
    }

    private static $isRegister = FALSE;
    public static function RegisterSessions()
    {
        if (!self::$isRegister) {
            self::RegisterObjectChildProcessor('sessions', 'sessionsProc');
            self::$isRegister = TRUE;
        }
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

    public function sessionsProc(array &$request, $parent)
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

}
