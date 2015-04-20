<?php

trait SessionManager
{

    public static function GetBySessionId($sessionId)
    {
        return self::GetOne('sessionId', $sessionId);
    }

    private static $isRegister = FALSE;
    public static function RegisterSessions()
    {
        if (!self::$isRegister) {
            self::RegisterObjectChildProcessor('sessions', 'sessionsProc');
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

