<?php

trait Processor
{

    private static function oneSegmentProcess(array &$request, $subject)
    {
        $childObject = FALSE;
        $child = array_shift($request['paths']);
        $classChildrenProcess = self::GetClassChildrenProcess($child);
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
            $childObject->ObjectChildrenProcess($grandson, $request);
            //print_r($request);
        } else {
            //print 'error';
            $classChildrenProcess = self::GetClassChildrenProcess($child);
            if ($classChildrenProcess) {
                $request = call_user_func(__CLASS__ . '::' . $classChildrenProcess, $request);
            } else {
                $request['response']['code'] = 404; //resource not found
            }
        }
    }

    private static function normalPush(array &$request)
    {
        $pathCount = count($request['paths']);
        switch ($pathCount) {
            case 0:
                switch ($request['method']) {
                    case 'POST':
                        self::NormalInsert($request);
                        break;
                    case 'PUT':
                        self::NormalUpdate($request);
                        break;
                    case 'PATCH':
                        self::NormalUpdate($request);
                        break;
                }
                break;
            case 1:
                $childObject = self::oneSegmentProcess($request, NULL);
                if ($childObject) {
                    switch ($request['method']) {
                        case 'POST':
                            $request['response']['code'] = 400; //bad request, resource exist
                            $request['response']['body'] = '{"state": "resource has exist"}';
                            break;
                        case 'PUT':
                            self::SingleUpdate($request, $childObject);
                            break;
                        case 'PATCH':
                            self::SingleUpdate($request, $childObject);
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
                self::NormalDelete($request);
                break;
            case 1:
                $childObject = self::oneSegmentProcess($request, NULL);
                if ($childObject) {
                    self::SingleDelete($request, $childObject);
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
                    $offset = $request['params']['offset'];
                    $length = $request['headers']['Content-Length'];
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
                        $request['response']['body'] = $childObject->downloadSlice('', $offset, $length);
                    } else {
                        $request['response']['body'] = $childObject->download('');
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
                            $request['response']['body'] = $childObject->downloadSlice('cover', $offset, $length);
                        } else {
                            $request['response']['body'] = $childObject->download('cover');
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

    private static function NormalInsert(array &$request)
    {
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
            $request['response']['body'] = '{ "newId" : ' . json_encode($ids) . ' }';
            //$request['response']['code'] = 500; //Internal server error
        } else {
            $request['response']['code'] = 400; //bad request
        }
    }

    private static function SingleInsert(array &$request, $id)
    {
        $data = self::ConvertBodyToObject($request['body']);
        if ($data) {
            $data->setId($id);
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
    }

    private static function NormalUpdate(array &$request)
    {
        $data = self::ConvertBodyToObject($request['body']);
        //@@add the filter by parent && regionExpression
        $filter = ConvertJsonToWhere($request['params']['filter']);
        $filter .= ' AND (' . $request['temp']['regionExpression'] . ')';
        $r = self::BatchUpdate($data, ' WHERE ' . $filter);
        if ($r) {
            $request['response']['code'] = 200; //ok
        } else {
            $request['response']['code'] = 500; //Internal server error
        }
    }

    private static function SingleUpdate(array &$request, $s)
    {
        $data = self::ConvertBodyToObject($request['body']);
        //$data->SetId(intval($child));
        //$r = $data->Update();
        $s->FillSelf($data);
        $r = $s->Update();
        if ($r) {
            $request['response']['code'] = 200; //ok
        } else {
            $request['response']['code'] = 404; //not found
        }
    }

    private static function NormalDelete(array &$request)
    {
        //@@add the filter by parent && regionExpression
        $filter = ConvertJsonToWhere($request['params']['filter']);
        $filter .= ' AND (' . $request['temp']['regionExpression'] . ')';
        $r = self::BatchDelete(' WHERE ' . $filter);
        if ($r) {
            $request['response']['code'] = 200; //ok
        } else {
            $request['response']['code'] = 404; //not found
        }
    }

    private static function SingleDelete(array &$request, $s)
    {
        //$r = self::BatchDelete(self::GetIdFilter($child));
        $r = $s->Delete();
        if ($r) {
            $request['response']['code'] = 200; //ok
        } else {
            $request['response']['code'] = 404; //not found
        }
    }

    private static function ConvertBodyToObject($json)
    {
        $data = json_decode($json, true);
        $className = __CLASS__;
        $result = new $className;
        $result->FillSelf((array)$data);
        return $result;
    }

    private static function ConvertBodyToArray($json)
    {
        $result = array();
        $data = json_decode($json, true);
        $className = __CLASS__;
        foreach ($data as $datum) {
            $item = new $className;
            $item->FillSelf((array)$datum);
            $result[] = $item;
        }
        return $result;
    }

    /*
    private static function NormalInsert(array &$request)
    {
        $pc = self::$parentClassName;
        $pc::NormalInsert($request);
        self::SelfInsert($request);
    }

    private static function SelfInsert(array &$request)
    {
        $sc = __CLASS__;
        $self = new $sc;
        $r = $request['response']['body'];
        $id = json_decode($r);
        $self->setOrganizationId($id->newId);
        $r = $self->Insert();
        if ($r) {
            $request['response']['code'] = 201; //created
            $request['response']['body'] = '{ "newId" : ' . $r . ' }';
        } else {
            $request['response']['code'] = 500; //Internal server error
        }
    }

    private static function NormalUpdate(array &$request)
    {
        $pc = self::$parentClassName;
        $pc::NormalUpdate($request);
        self::SelfUpdate($request);
    }

    private static function SelfUpdate(array &$request)
    {
        //
    }

    private static function SingleUpdate(array &$request, $s)
    {
        $organization = $s->getOrganization();
        $pc = self::$parentClassName;
        $pc::SingleUpdate($request, $organization);
        self::SelfSingleUpdate($request, $s);
    }

    private static function SelfSingleUpdate($request, $s)
    {
        //
    }

    private static function NormalDelete(array &$request)
    {
        $result = $request['response'];
        $pc = self::$parentClassName;
        $pc::NormalDelete($request);
        self::SelfDelete($request);
    }

    private static function SelfDelete(array &$request)
    {
        //@@think think think
        $r = self::BatchDelete($request['params']['filter']);
        if ($r) {
            $request['response']['code'] = 200; //ok
        } else {
            $request['response']['code'] = 404; //not found
        }
    }

    private static function SingleDelete(array &$request, $s)
    {
        $result = $request['response'];
        $organization = $s->getOrganization();
        $pc = self::$parentClassName;
        $pc::SingleDelete($request, $organization);
        self::SelfSingleDelete($request, $s);
    }

    private static function SelfSingleDelete(array &$request, $s)
    {
        $r = $s->Delete();
        if ($r) {
            $request['response']['code'] = 200; //ok
        } else {
            $request['response']['code'] = 404; //not found
        }
    }

    private static function NormalSelect(array &$request)
    {
        //@@think think think
        $pc = self::$parentClassName;
        $lists = $pc::Select($request['params']);
        if (count($lists) == 0) {
            $request['response']['code'] = 404; //Not Found
        } else {
            $request['response']['body'] = self::ToArrayJson($lists);
        }
    }

    private static function SelfSelect(array &$request)
    {
        //
    }
     */

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
        $acceptContentType = $request['headers']['Accept'];
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
                //print $acceptContentType;
                if (TRUE/*strpos($acceptContentType, 'application/json') === 0*/) {
                    //normal
                    self::normalPull($request);
                } else {
                    //binary downloader
                    self::binaryPull($request);
                }
                break;
            case 'DELETE':
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
