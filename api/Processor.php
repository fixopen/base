<?php

trait Processor
{

    private static function BeforePost(array &$request)
    {
        $request['temp']['continue'] = TRUE;
    }

    private static function AfterPost(array &$request)
    {
        $request['temp']['continue'] = TRUE;
    }

    private static function BeforePut(array &$request)
    {
        $request['temp']['continue'] = TRUE;
    }

    private static function AfterPut(array &$request)
    {
        $request['temp']['continue'] = TRUE;
    }

    private static function BeforePatch(array &$request)
    {
        $request['temp']['continue'] = TRUE;
    }

    private static function AfterPatch(array &$request)
    {
        $request['temp']['continue'] = TRUE;
    }

    private static function BeforeGet(array &$request)
    {
        $request['temp']['continue'] = TRUE;
    }

    private static function AfterGet(array &$request)
    {
        $request['temp']['continue'] = TRUE;
    }

    private static function BeforeDelete(array &$request)
    {
        $request['temp']['continue'] = TRUE;
    }

    private static function AfterDelete(array &$request)
    {
        $request['temp']['continue'] = TRUE;
    }

    public function FillSelfByStdClassObject($o)
    {
        $class = new ReflectionClass($o);
        foreach (self::$types as $key => $typeName) {
            $value = NULL;
            if ($class->hasProperty($key)) {
                $value = $o->$key;
            }
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

    public static function dispatcher(array &$request)
    {
        if ($request['temp']['continue']) {
            $child = array_shift($request['paths']);
            $childObject = self::IsPrimaryKey($child);
            if ($childObject) {
                //self::Process($request, $childObject);
                $childObject->ObjectChildrenProcess($request);
            } else {
                $classChildProc = self::GetClassChildrenProcessor($child);
                if ($classChildProc) {
                    $staticMethod = __CLASS__ . '::' . $classChildProc;
                    $staticMethod($request);
                } else {
                    $request['response']['code'] = 404;
                    $request['temp']['continue'] = FALSE;
                }
            }
        }
    }

    public static function Process(array &$request, $parent)
    {
        //print 'process start<br />';
        //$subject = self::GetSubjectByQuery($request);
        $subject = NULL;
        $attributeBag = '';

        if ($subject) {
            $regionExpression = self::CheckPermission($subject, $request['method'], dataTypes::GetIdByName(self::$tableName), $attributeBag);
            if (!$regionExpression) {
                $request['response']['code'] = 401; //Unauthorized
                return;
            }
            $request['temp']['regionExpression'] = $regionExpression;
        } else {
            //only for login
            $request['temp']['regionExpression'] = '1 = 1';
        }

        $request['temp']['parent'] = $parent;

        if ($request['body'] == '') {
            $type = $request['headers']['Accept'];
        } else {
            $type = $request['headers']['Content-Type'];
        }
        $pathCount = count($request['paths']);
        $method = $request['method'];
        if (strpos($type, 'application/json') === 0) {
            $request['temp']['type'] = 'normal';
            if ($pathCount == 0) {
                $className = __CLASS__;
                switch ($method) {
                    case 'POST':
                        self::BeforePost($request);
                        if (!$request['temp']['continue']) {
                            return;
                        }
                        $r = 0;
                        $data = json_decode($request['body']);
                        if (is_array($data)) {
                            foreach ($data as $datum) {
                                $object = new $className;
                                $object->FillSelfByStdClassObject($datum);
                                $r = $object->Insert();
                            }
                        } else {
                            $object = new $className;
                            $object->FillSelfByStdClassObject($data);
                            $r = $object->Insert();
                        }
                        if ($r) {
                            $request['response']['body'] = json_encode($object);
                        } else {
                            $request['response']['code'] = 400;
                            $request['temp']['continue'] = FALSE;
                        }
                        if ($request['temp']['continue']) {
                            self::AfterPost($request);
                        }
                        break;
                    case 'PUT':
                        self::BeforePut($request);
                        if (!$request['temp']['continue']) {
                            return;
                        }
                        $data = json_decode($request['body']);
                        if (is_array($data)) {
                            foreach ($data as $datum) {
                                $object = new $className;
                                $object->FillSelfByStdClassObject($datum);
                                $object->Update(FALSE);
                            }
                        } else {
                            $object = new $className;
                            $object->FillSelfByStdClassObject($data);
                            if ($parent) {
                                if (!isset($object->id)) {
                                    $object->id = $parent->id;
                                }
                                if ($parent->id == $object->id) {
                                    $object->Update(FALSE);
                                    $request['response']['body'] = json_encode($object);
                                } else {
                                    $request['response']['code'] = 404;
                                    $request['temp']['continue'] = FALSE;
                                }
                            } else {
                                $filter = $request['params']['filter'];
                                $where = self::ConvertJsonToWhere($filter);
                                self::BatchUpdate($object, $where, FALSE);
                            }
                        }
                        if ($request['temp']['continue']) {
                            self::AfterPut($request);
                        }
                        break;
                    case 'PATCH':
                        self::BeforePatch($request);
                        if (!$request['temp']['continue']) {
                            return;
                        }
                        $data = json_decode($request['body']);
                        if (is_array($data)) {
                            foreach ($data as $datum) {
                                $object = new $className;
                                $object->FillSelfByStdClassObject($datum);
                                $object->Update(TRUE);
                            }
                        } else {
                            if ($parent) {
                                $parent->FillSelfByStdClassObject($data);
                                $r = $parent->Update(TRUE);
                                if ($r) {
                                    $request['response']['body'] = json_encode($parent);
                                } else {
                                    $request['response']['code'] = 400;
                                    $request['temp']['continue'] = FALSE;
                                }
                            } else {
                                $object = new $className;
                                $object->FillSelfByStdClassObject($data);
                                $filter = $request['params']['filter'];
                                $where = self::ConvertJsonToWhere($filter);
                                self::BatchUpdate($object, $where, TRUE);
                            }
                        }
                        if ($request['temp']['continue']) {
                            self::AfterPatch($request);
                        }
                        break;
                    case 'GET':
                        self::BeforeGet($request);
                        if (!$request['temp']['continue']) {
                            return;
                        }
                        if ($parent) {
                            $request['response']['body'] = json_encode($parent);
                        } else {
                            $data = self::Select($request['params'], $request['temp']['regionExpression']);
                            $request['response']['body'] = json_encode($data);
                        }
                        if ($request['temp']['continue']) {
                            self::AfterGet($request);
                        }
                        break;
                    case 'DELETE':
                        self::BeforeDelete($request);
                        if (!$request['temp']['continue']) {
                            return;
                        }
                        if ($parent) {
                            $parent->Delete();
                        } else {
                            $filter = $request['params']['filter'];
                            $where = self::ConvertJsonToWhere($filter);
                            self::BatchDelete($where);
                        }
                        if ($request['temp']['continue']) {
                            self::AfterDelete($request);
                        }
                        break;
                    default:
                        $request['response']['code'] = 405; //method not allow
                        $request['temp']['continue'] = FALSE;
                        break;
                }
            } else {
                self::dispatcher($request);
            }
        } else {
            $request['temp']['type'] = 'binary';
            switch ($pathCount) {
                case 0:
                    $request['response']['code'] = 400;
                    $request['temp']['continue'] = FALSE;
                    break;
                case 1:
                    $child = array_shift($request['paths']);
                    $childObject = self::IsPrimaryKey($child);
                    if ($childObject) {
                        switch ($method) {
                            case 'POST':
                            case 'PUT':
                            case 'PATCH':
                                $child = array_shift($request['paths']);
                                $childObject = self::IsPrimaryKey($child);
                                $offset = $request['params']['offset'];
                                $length = $request['headers']['Content-Length'];
                                if ($offset != -1 && $length != -1) {
                                    $childObject->uploadFileSlice($request['body'], $offset, $length);
                                } else {
                                    $childObject->uploadFile($request['body']);
                                }
                                break;
                            case 'GET':
                                $range = $request['headers']['Range'];
                                if ($range) {
                                    //bytes=startPos-stopPos, ...
                                    $areas = explode(',', $range);
                                    foreach ($areas as $area) {
                                        $pair = explode('-', $area);
                                        if (count($pair) == 2) {
                                            $startPos = intval($pair[0]);
                                            $stopPos = intval($pair[1]);
                                            $offset = $startPos;
                                            $length = $stopPos - $startPos + 1;
                                        }
                                    }
                                }
                                if ($offset != -1 && $length != -1) {
                                    $request['response']['Content-Type'] = $childObject->mimeType;
                                    $request['response']['body'] = $childObject->downloadFileSlice($offset, $length);
                                } else {
                                    $request['response']['Content-Type'] = $childObject->mimeType;
                                    $c = $childObject->downloadFile();
                                    $request['response']['headers']['Content-Length'] = $c['length'];
                                    $request['response']['body'] = $c['content'];
                                }
                                break;
                            case 'DELETE':
                                $childObject->deleteFile();
                                break;
                            default:
                                $request['response']['code'] = 405; //method not allow
                                $request['temp']['continue'] = FALSE;
                                break;
                        }
                    } else {
                        $request['response']['code'] = 404; //bad request
                        $request['temp']['continue'] = FALSE;
                    }
                    break;
                default:
                    self::dispatcher($request);
                    break;
            }
        }
    }

}
