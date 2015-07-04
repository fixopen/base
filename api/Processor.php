<?php

trait Processor
{

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
            if ($pathCount == 0) {
                $className = __CLASS__;
                switch ($method) {
                    case 'POST':
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
                        }
                        break;
                    case 'PUT':
                        $data = json_decode($request['body']);
                        if (is_array($data)) {
                            foreach ($data as $datum) {
                                $object = new $className;
                                $object->FillSelfByStdClassObject($datum);
                                $object->Update();
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
                                }
                            } else {
                                $filter = $request['params']['filter'];
                                $where = self::ConvertJsonToWhere($filter);
                                self::BatchUpdate($object, $where, FALSE);
                            }
                        }
                        break;
                    case 'PATCH':
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
                                }
                            } else {
                                $object = new $className;
                                $object->FillSelfByStdClassObject($data);
                                $filter = $request['params']['filter'];
                                $where = self::ConvertJsonToWhere($filter);
                                self::BatchUpdate($object, $where, TRUE);
                            }
                        }
                        break;
                    case 'GET':
                        if ($parent) {
                            $request['response']['body'] = json_encode($parent);
                        } else {
                            $data = self::Select($request['params'], $request['temp']['regionExpression']);
                            $request['response']['body'] = json_encode($data);
                        }
                        break;
                    case 'DELETE':
                        if ($parent) {
                            $parent->Delete();
                        } else {
                            $filter = $request['params']['filter'];
                            $where = self::ConvertJsonToWhere($filter);
                            self::BatchDelete($where);
                        }
                        break;
                    default:
                        $request['response']['code'] = 405; //method not allow
                        break;
                }
            } else {
                self::dispatcher($request);
            }
        } else {
            switch ($pathCount) {
                case 0:
                    $request['response']['code'] = 400;
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
                                    $childObject->uploadSlice($request['body'], $offset, $length);
                                } else {
                                    $childObject->upload($request['body']);
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
                                    $request['response']['body'] = $childObject->downloadSlice($offset, $length);
                                } else {
                                    $request['response']['Content-Type'] = $childObject->mimeType;
                                    $c = $childObject->download();
                                    $request['response']['headers']['Content-Length'] = $c['length'];
                                    $request['response']['body'] = $c['content'];
                                }
                                break;
                            case 'DELETE':
                                $childObject->deleteFile();
                                break;
                            default:
                                $request['response']['code'] = 405; //method not allow
                                break;
                        }
                    } else {
                        $request['response']['code'] = 404; //bad request
                    }

                    break;
                default:
                    self::dispatcher($request);
                    break;
            }
        }
    }

}
