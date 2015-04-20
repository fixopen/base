<?php

trait Processor
{

    private static function oneSegmentProcess(array &$request, $subject)
    {
        $childObject = FALSE;
        $child = array_shift($request['paths']);
        $request['temp']['child'] = $child;
        $classChildrenProcess = self::GetClassChildrenProcessor($child);
        if ($classChildrenProcess) {
            $request = call_user_func(__CLASS__ . '::' . $classChildrenProcess, $request);
        } else {
            if ($child == 'me') {
                $childObject = $subject;
            }
            if (!$childObject) {
                $childObject = self::IsPrimaryKey($child);
            }
        }
        return $childObject;
    }

    private static function manySegmentProcess(array &$request, $subject)
    {
        $child = array_shift($request['paths']);
        //print 'child is ' . $child;
        $childObject = self::IsPrimaryKey($child);
        if ($child == 'me') {
            $childObject = $subject;
        }
        //print_r($childObject);
        if ($childObject) {
            $grandson = array_shift($request['paths']);
            $subResourceProc = self::GetObjectChildProcessor($grandson);
            if ($subResourceProc) {
                $childObject->$subResourceProc($grandson, $request);
                //call_user_func(array($childObject, $subResourceProc), $request);

                //$childObject->ObjectChildrenProcess($grandson, $request);
                //print_r($request);
            } else {
                $grandson::Process($request, $childObject);
            }
        } else {
            //print 'error';
            $classChildrenProcess = self::GetClassChildrenProcessor($child);
            if ($classChildrenProcess) {
                $fullName = __CLASS__ . '::' . $classChildrenProcess;
                $fullName($request);
                //$request = call_user_func(__CLASS__ . '::' . $classChildrenProcess, $request);
            } else {
                $request['response']['code'] = 404; //resource not found
            }
        }
    }

    private static function normalPush(array &$request)
    {
        $pathCount = count($request['paths']);
        switch ($pathCount) {
            case 0: //batch
                switch ($request['method']) {
                    case 'POST': //batch insert
                        $data = self::ConvertBodyToArray($request['body']);
                        if ($data) {
                            $ids = array();
                            foreach ($data as $item) {
                                $r = $item->Insert();
                                if ($r) {
                                    $ids[] = $r;
                                } else {
                                    $ids[] = NULL;
                                }
                            }
                            $request['response']['code'] = 201; //created
                            $request['response']['body'] = self::ToArrayJson($data);
                            //$request['response']['code'] = 500; //Internal server error
                        } else {
                            $request['response']['code'] = 400; //bad request
                        }
                        break;
                    case 'PUT': //batch update
                        $data = self::ConvertBodyToObject($request['body']);
                        //@@add the filter by parent && regionExpression
                        $filter = ConvertJsonToWhere($request['params']['filter']);
                        $filter .= ' AND (' . $request['temp']['regionExpression'] . ')';
                        $r = self::CustomSelect(' WHERE ' . $filter);
                        foreach ($r as $item) {
                            $item->Delete();
                            $data->id = $item->id;
                            $data->Insert();
                        }
                        $request['response']['body'] = self::ToArrayJson(self::CustomSelect(' WHERE ' . $filter));
                        break;
                    case 'PATCH':
                        $data = self::ConvertBodyToObject($request['body']);
                        //@@add the filter by parent && regionExpression
                        $filter = ConvertJsonToWhere($request['params']['filter']);
                        $filter .= ' AND (' . $request['temp']['regionExpression'] . ')';
                        $r = self::BatchUpdate($data, ' WHERE ' . $filter);
                        if ($r) {
                            $request['response']['body'] = self::ToArrayJson(self::CustomSelect(' WHERE ' . $filter));
                        } else {
                            $request['response']['code'] = 500; //Internal server error
                        }
                        break;
                }
                break;
            case 1: //single
                $child = array_shift($request['paths']);
                $classChildrenProcess = self::GetClassChildrenProcess($child);
                if ($classChildrenProcess) {
                    $request = call_user_func(__CLASS__ . '::' . $classChildrenProcess, $request);
                } else {
                    $childObject = self::IsPrimaryKey($child);
                    if ($childObject) {
                        switch ($request['method']) {
                            case 'POST':
                                $request['response']['code'] = 400; //bad request, resource exist
                                $request['response']['body'] = '{"state": "resource has exist"}';
                                break;
                            case 'PUT':
                                $data = self::ConvertBodyToObject($request['body']);
                                $r = $data->Update();
                                if ($r) {
                                    $request['response']['code'] = 200; //ok
                                } else {
                                    $request['response']['code'] = 404; //not found
                                }
                                break;
                            case 'PATCH':
                                $data = self::ConvertBodyToObject($request['body']);
                                $childObject->FillSelfByJson($data);
                                $r = $childObject->Update();
                                if ($r) {
                                    $request['response']['code'] = 200; //ok
                                } else {
                                    $request['response']['code'] = 404; //not found
                                }
                                break;
                        }
                    } else {
                        switch ($request['method']) {
                            case 'POST':
                                $data = self::ConvertBodyToObject($request['body']);
                                if ($data) {
                                    $data->setId($request['temp']['child']);
                                    $r = $data->Insert();
                                    if ($r) {
                                        $request['response']['code'] = 201; //created
                                        $request['response']['body'] = $data->ToJSON();
                                    } else {
                                        $request['response']['code'] = 500; //Internal server error
                                    }
                                } else {
                                    $request['response']['code'] = 400; //bad request
                                }
                                break;
                            case 'PUT':
                            case 'PATCH':
                                $request['response']['code'] = 400; //bad request, resource not exist
                                $request['response']['body'] = '{"state": "resource not exist"}';
                                break;
                        }
                    }
                }
                $subResourceProc = self::GetObjectChildProcess($child);

                $childObject = self::oneSegmentProcess($request, NULL);
                if ($childObject) {
                    switch ($request['method']) {
                        case 'POST':
                            $request['response']['code'] = 400; //bad request, resource exist
                            $request['response']['body'] = '{"state": "resource has exist"}';
                            break;
                        case 'PUT':
                            $data = self::ConvertBodyToObject($request['body']);
                            $r = $data->Update();
                            if ($r) {
                                $request['response']['code'] = 200; //ok
                            } else {
                                $request['response']['code'] = 404; //not found
                            }
                            break;
                        case 'PATCH':
                            $data = self::ConvertBodyToObject($request['body']);
                            $childObject->FillSelfByJson($data);
                            $r = $childObject->Update();
                            if ($r) {
                                $request['response']['code'] = 200; //ok
                            } else {
                                $request['response']['code'] = 404; //not found
                            }
                            break;
                    }
                } else {
                    switch ($request['method']) {
                        case 'POST':
                            $data = self::ConvertBodyToObject($request['body']);
                            if ($data) {
                                $data->setId($request['temp']['child']);
                                $r = $data->Insert();
                                if ($r) {
                                    $request['response']['code'] = 201; //created
                                    $request['response']['body'] = $data->ToJSON();
                                } else {
                                    $request['response']['code'] = 500; //Internal server error
                                }
                            } else {
                                $request['response']['code'] = 400; //bad request
                            }
                            break;
                        case 'PUT':
                        case 'PATCH':
                            $request['response']['code'] = 400; //bad request, resource not exist
                            $request['response']['body'] = '{"state": "resource not exist"}';
                            break;
                    }
                }
                break;
            default:
                self::manySegmentProcess($request, NULL);
                break;
        }
    }

    private static function normalPull(array &$request)
    {
        $pathCount = count($request['paths']);
        switch ($pathCount) {
            case 0:
                $lists = self::Select($request['params'], $request['temp']['regionExpression']);
                //print_r($lists);
                if (count($lists) == 0) {
                    $request['response']['code'] = 404; //Not Found
                } else {
                    $request['response']['body'] = self::ToArrayJson($lists);
                }
                break;
            case 1:
                $childObject = self::oneSegmentProcess($request, NULL);
                if ($childObject) {
                    $request['response']['body'] = $childObject->ToJson();
                } else {
                    $request['response']['code'] = 404; //resource not found
                }
                break;
            default:
                self::manySegmentProcess($request, NULL);
                break;
        }
    }

    private static function normalRemove(array &$request)
    {
        $pathCount = count($request['paths']);
        switch ($pathCount) {
            case 0:
                //@@add the filter by parent && regionExpression
                $filter = ConvertJsonToWhere($request['params']['filter']);
                $filter .= ' AND (' . $request['temp']['regionExpression'] . ')';
                $r = self::BatchDelete(' WHERE ' . $filter);
                if ($r) {
                    $request['response']['code'] = 200; //ok
                } else {
                    $request['response']['code'] = 404; //not found
                }
                break;
            case 1:
                $childObject = self::oneSegmentProcess($request, NULL);
                if ($childObject) {
                    //$r = self::BatchDelete(self::GetIdFilter($child));
                    $r = $childObject->Delete();
                    if ($r) {
                        $request['response']['code'] = 200; //ok
                    } else {
                        $request['response']['code'] = 404; //not found
                    }
                } else {
                    $request['response']['code'] = 404; //resource not found
                }
                break;
            default:
                self::manySegmentProcess($request, NULL);
                break;
        }
    }

    private static function binaryPush(array &$request)
    {
        $pathCount = count($request['paths']);
        switch ($pathCount) {
            case 1:
                $child = array_shift($request['paths']);
                $childObject = self::IsPrimaryKey($child);
                if ($childObject) {
                    $offset = $request['params']['offset'];
                    $length = $request['headers']['Content-Length'];
                    if ($offset != -1 && $length != -1) {
                        $childObject->uploadSlice('', $request['body'], $offset, $length);
                    } else {
                        $childObject->upload('', $request['body']);
                    }
                } else {
                    $request['response']['code'] = 400; //bad request
                    $request['response']['body'] = '{"state": "resource not found"}';
                }
                break;
            case 2: //for books/{id}/cover
                $child = array_shift($request['paths']);
                $childObject = self::IsPrimaryKey($child);
                if ($childObject) {
                    $grandson = array_shift($request['paths']);
                    if ($grandson == 'cover') {
                        $offset = $request['params']['offset'];
                        $length = $request['headers']['Content-Length'];
                        if ($offset != -1 && $length != -1) {
                            $childObject->uploadSlice('cover', $request['body'], $offset, $length);
                        } else {
                            $childObject->upload('cover', $request['body']);
                        }
                    } else {
                        $request['response']['code'] = 400; //bad request
                        $request['response']['body'] = '{"state": "not recognize branch, must is [cover]"}';
                    }
                } else {
                    $request['response']['code'] = 400; //bad request
                    $request['response']['body'] = '{"state": "resource not found"}';
                }
                break;
            default:
                $request['response']['code'] = 400; //bad request
                $request['response']['body'] = '{"state": "path segment too much"}';
                break;
        }
    }

    private static function binaryPull(array &$request)
    {
        $pathCount = count($request['paths']);
        switch ($pathCount) {
            case 1:
                $child = array_shift($request['paths']);
                $childObject = self::IsPrimaryKey($child);
                if ($childObject) {
                    $offset = -1;
                    $length = -1;
//                    $offset = $request['params']['offset'];
//                    $length = $request['headers']['Content-Length'];
//                    $range = $request['headers']['Range'];
//                    if ($range) {
//                        //bytes=startPos-stopPos, ...
//                        $areas = explode(',', $range);
//                        foreach ($areas as $area) {
//                            $pair = explode('-', $area);
//                            if (count($pair) == 2) {
//                                $startPos = intval($pair[0]);
//                                $stopPos = intval($pair[1]);
//                                $offset = $startPos;
//                                $length = $stopPos - $startPos + 1;
//                            }
//                        }
//
//                    }
                    if ($offset != -1 && $length != -1) {
                        $request['response']['body'] = $childObject->downloadSlice('', $offset, $length);
                    } else {
                        $request['response']['Content-Type'] = $childObject->mimeType;
                        $c = $childObject->download('');
                        $request['response']['headers']['Content-Length'] = $c['length'];
                        $request['response']['body'] = $c['content'];
                    }
                } else {
                    $request['response']['code'] = 400; //bad request
                    $request['response']['body'] = '{"state": "resource not found"}';
                }
                break;
            case 2: //for books/{id}/cover
                $child = array_shift($request['paths']);
                $childObject = self::IsPrimaryKey($child);
                if ($childObject) {
                    $grandson = array_shift($request['paths']);
                    if ($grandson == 'cover') {
                        $offset = -1;
                        $length = -1;
//                        $offset = $request['params']['offset'];
//                        $length = $request['headers']['Content-Length'];
//                        $range = $request['headers']['Range'];
//                        if ($range) {
//                            //bytes=startPos-stopPos, ...
//                            $areas = explode(',', $range);
//                            foreach ($areas as $area) {
//                                $pair = explode('-', $area);
//                                if (count($pair) == 2) {
//                                    $startPos = intval($pair[0]);
//                                    $stopPos = intval($pair[1]);
//                                    $offset = $startPos;
//                                    $length = $stopPos - $startPos + 1;
//                                }
//                            }
//                        }
                        if ($offset != -1 && $length != -1) {
                            $request['response']['body'] = $childObject->downloadSlice('cover', $offset, $length);
                        } else {
                            $request['response']['Content-Type'] = $childObject->mimeType;
                            $c = $childObject->download('cover');
                            $request['response']['headers']['Content-Length'] = $c['length'];
                            $request['response']['body'] = $c['content'];
                        }
                    } else {
                        $request['response']['code'] = 400; //bad request
                        $request['response']['body'] = '{"state": "not recognize branch, must is [cover]"}';
                    }
                } else {
                    $request['response']['code'] = 400; //bad request
                    $request['response']['body'] = '{"state": "resource not found"}';
                }
                break;
            default:
                $request['response']['code'] = 400; //bad request
                $request['response']['body'] = '{"state": "path segment too much"}';
                break;
        }
    }

    private static function binaryRemove(array &$request)
    {
        $pathCount = count($request['paths']);
        switch ($pathCount) {
            case 1:
                $child = array_shift($request['paths']);
                $childObject = self::IsPrimaryKey($child);
                if ($childObject) {
                    //delete the file
                    unlink($childObject->getContent(''));
                    //$request['response']['code'] = 405; //method not allow
                } else {
                    $request['response']['code'] = 400; //bad request
                    $request['response']['body'] = '{"state": "resource not found"}';
                }
                break;
            case 2: //for books/{id}/cover
                $child = array_shift($request['paths']);
                $childObject = self::IsPrimaryKey($child);
                if ($childObject) {
                    $grandson = array_shift($request['paths']);
                    if ($grandson == 'cover') {
                        //delete the file
                        unlink($childObject->getContent('cover'));
                        //$request['response']['code'] = 405; //method not allow
                    } else {
                        $request['response']['code'] = 400; //bad request
                        $request['response']['body'] = '{"state": "not recognize branch, must is [cover]"}';
                    }
                } else {
                    $request['response']['code'] = 400; //bad request
                    $request['response']['body'] = '{"state": "resource not found"}';
                }
                break;
            default:
                $request['response']['code'] = 400; //bad request
                $request['response']['body'] = '{"state": "path segment too much"}';
                break;
        }
    }

    private static function ConvertBodyToObject($json)
    {
        $data = json_decode($json, true);
        $className = __CLASS__;
        $result = new $className;
        $result->FillSelfByJson((array)$data);
        return $result;
    }

    private static function ConvertBodyToArray($json)
    {
        $result = array();
        $data = json_decode($json, true);
        $className = __CLASS__;
        foreach ($data as $datum) {
            $item = new $className;
            $item->FillSelfByJson((array)$datum);
            $result[] = $item;
        }
        return $result;
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

        //print 'permission check finally<br />';
        switch ($request['method']) {
            case 'POST':
            case 'PUT':
            case 'PATCH':
                $requestContentType = $request['headers']['Content-Type'];
                if (strpos($requestContentType, 'application/json') === 0) {
                    //normal
                    self::normalPush($request);
                } else {
                    //binary uploader
                    self::binaryPush($request);
                }
                break;
            case 'GET':
                $acceptContentType = $request['headers']['Accept'];
                //print $acceptContentType;
                if (strpos($acceptContentType, 'application/json') === 0) {
                    //normal
                    self::normalPull($request);
                } else {
                    //binary downloader
                    self::binaryPull($request);
                }
                break;
            case 'DELETE':
                $acceptContentType = $request['headers']['Accept'];
                if (strpos($acceptContentType, 'application/json') === 0) {
                    //normal delete
                    self::normalRemove($request);
                } else {
                    //binary delete
                    self::binaryRemove($request);
                }
                break;
            default:
                $request['response']['code'] = 405; //method not allow
                $request['response']['body'] = '{"state": "method not allow"}';
                break;
        }
    }

}
