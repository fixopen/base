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
        $session = self::GetOne('sessionId', $sessionId);
        if ($session) {
            $session->setLastOperationTime(time());
            $session->Update();
        }
    }

}
